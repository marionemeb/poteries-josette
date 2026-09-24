<?php

/*
 * One-shot deploy helper. .github/workflows/deploy.yml uploads it next to
 * index.php under a random name, with a random token baked in, drives it over
 * HTTPS, and it deletes itself at the end. It is not part of the app.
 *
 * Why: the hosting only has FTP (no SSH), and syncing vendor/ file by file
 * over FTP gets cut off by OVH after ~10k commands (2026-09-24). So the
 * workflow uploads one release zip to the FTP home (outside anything served
 * over HTTP) and this script does the file work on the server:
 *
 *   check     read-only: PHP version, zip support, paths, write access
 *   extract   unzip the release into www_new/, and list what only exists on
 *             the server in www/ (.env, .htaccess files, Josette's uploaded
 *             photos, logs...) so it can be carried over
 *   swap      move those server-only files into www_new/, then rename
 *             www -> www_prev and www_new -> www (the switch is two renames)
 *   finish    delete this script from both trees, the zip, and old releases
 *   rollback  put www_prev back in place, moving the server-only files back
 *
 * www_prev is kept until the next deploy, as a manual rollback point.
 */

const TOKEN = '__DEPLOY_TOKEN__';

/**
 * Trees the release owns completely: whatever the server has there that the
 * release doesn't is stale (e.g. EasyAdmin 2's YAML config) and is dropped.
 * Outside of them, a file missing from the release is dropped too if the
 * repository tracks it (tests/, docker/... shipped by the old FTP sync), and
 * carried over otherwise: those are the server-only files.
 */
const RELEASE_OWNED = [
    'assets', 'bin', 'config', 'src', 'templates', 'translations', 'vendor',
    'public/build', 'public/bundles', 'var/cache',
];

/** Server-side leftovers of the previous FTP sync tool, dropped too. */
const DROPPED = ['.ftp-deploy-sync-state.json'];

header('Content-Type: application/json');

if (TOKEN === '__DEPLOY'.'_TOKEN__' || !hash_equals(TOKEN, $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? '')) {
    respond(403, 'forbidden');
}

$www = dirname(__DIR__);
$home = dirname($www);
$name = basename($www);
$paths = [
    'www' => $www,
    'new' => "$home/{$name}_new",
    'prev' => "$home/{$name}_prev",
    'failed' => "$home/{$name}_failed",
    'zip' => "$home/release.zip",
    'state' => "$home/deploy-state.json",
];
$self = 'public/'.basename(__FILE__);

set_time_limit(600);
ignore_user_abort(true);

try {
    switch ($_GET['step'] ?? '') {
        case 'check':
            respond(200, 'ok', [
                'php' => PHP_VERSION,
                'zip' => class_exists(ZipArchive::class),
                'www' => $www,
                'home_writable' => is_writable($home),
                'www_writable' => is_writable($www),
                'release_zip' => is_file($paths['zip']) ? filesize($paths['zip']) : null,
                'leftovers' => array_values(array_filter(['new', 'prev', 'failed'], fn ($k) => file_exists($paths[$k]))),
            ]);

        case 'extract':
            if (!class_exists(ZipArchive::class)) {
                throw new RuntimeException('PHP zip extension missing');
            }
            // Leftovers of earlier runs are set aside (renames are instant);
            // they're deleted by the finish step.
            foreach (['new', 'prev', 'failed'] as $k) {
                if (file_exists($paths[$k])) {
                    rename_or_fail($paths[$k], "$home/{$name}_trash_".$k.'_'.date('YmdHis'));
                }
            }
            $zip = new ZipArchive();
            if (true !== $zip->open($paths['zip'])) {
                throw new RuntimeException('cannot open '.$paths['zip']);
            }
            mkdir($paths['new'], 0705);
            if (!$zip->extractTo($paths['new'])) {
                throw new RuntimeException('extraction failed');
            }
            $files = $zip->numFiles;
            $zip->close();

            $moves = [];
            plan($www, $paths['new'], '', $self, repo_paths($paths['new'].'/.release-files'), $moves);
            save_state($paths['state'], ['status' => 'extracted', 'moves' => $moves, 'moved' => []]);
            respond(200, 'extracted', ['files' => $files, 'carried_over' => $moves]);

        case 'swap':
            $state = load_state($paths['state'], 'extracted');
            foreach ($state['moves'] as $rel) {
                rename_or_fail("$www/$rel", $paths['new']."/$rel");
                $state['moved'][] = $rel;
                save_state($paths['state'], $state);
            }
            // So the finish/rollback steps can still be reached once the new
            // tree is live.
            copy_or_fail(__FILE__, $paths['new']."/$self");
            rename_or_fail($www, $paths['prev']);
            if (!@rename($paths['new'], $www)) {
                rename_or_fail($paths['prev'], $www);
                move_back($state['moved'], $paths['new'], $www);
                save_state($paths['state'], ['status' => 'swap_failed'] + $state);
                throw new RuntimeException('could not rename the new release into place; old release restored');
            }
            $state['status'] = 'swapped';
            save_state($paths['state'], $state);
            reset_opcache();
            respond(200, 'swapped', ['carried_over' => $state['moved']]);

        case 'rollback':
            $state = load_state($paths['state'], 'swapped');
            rename_or_fail($www, $paths['failed']);
            rename_or_fail($paths['prev'], $www);
            move_back($state['moved'], $paths['failed'], $www);
            @unlink("$www/$self");
            @unlink($paths['failed']."/$self");
            save_state($paths['state'], ['status' => 'rolled_back'] + $state);
            reset_opcache();
            respond(200, 'rolled back', ['moved_back' => $state['moved']]);

        case 'finish':
            load_state($paths['state'], 'swapped');
            @unlink($paths['prev']."/$self");
            @unlink("$www/$self");
            @unlink($paths['zip']);
            save_state($paths['state'], ['status' => 'done', 'at' => date('c')]);
            $trash = glob("$home/{$name}_trash_*", GLOB_ONLYDIR) ?: [];
            respond(200, 'done', ['deleting_old_releases' => array_map('basename', $trash)], false);
            // Old releases can hold tens of thousands of files: answer first,
            // then delete. Whatever doesn't finish is retried next deploy.
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            foreach ($trash as $dir) {
                remove_tree($dir);
            }
            exit;

        default:
            respond(400, 'unknown step');
    }
} catch (Throwable $e) {
    respond(500, $e->getMessage());
}

/**
 * Lists, relative to the tree root, what exists in $old but not in $new,
 * outside release-owned trees. Directories missing from $new are listed as a
 * whole (one rename moves them); directories present in both are walked.
 */
function plan(string $old, string $new, string $rel, string $self, array $repo, array &$moves): void
{
    foreach (scandir($rel === '' ? $old : "$old/$rel") as $entry) {
        if ('.' === $entry || '..' === $entry) {
            continue;
        }
        $path = '' === $rel ? $entry : "$rel/$entry";
        if ($path === $self || in_array($path, DROPPED, true) || is_release_owned($path)) {
            continue;
        }
        if (!file_exists("$new/$path") && !is_link("$new/$path")) {
            if (!isset($repo[$path])) {
                $moves[] = $path;
            }
        } elseif (is_dir("$old/$path") && !is_link("$old/$path") && is_dir("$new/$path")) {
            plan($old, $new, $path, $self, $repo, $moves);
        }
        // Otherwise the release has its own version of that file: it wins.
    }
}

/**
 * The tracked files listed in the release, plus every directory above them.
 * A directory in there that the release doesn't ship (tests/...) is dropped
 * as a whole.
 */
function repo_paths(string $list): array
{
    $lines = is_file($list) ? file($list, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : false;
    if (!$lines) {
        throw new RuntimeException("missing or empty $list");
    }
    $paths = [];
    foreach ($lines as $file) {
        for ($path = $file; '.' !== $path && '' !== $path; $path = dirname($path)) {
            $paths[$path] = true;
        }
    }

    return $paths;
}

function is_release_owned(string $path): bool
{
    foreach (RELEASE_OWNED as $owned) {
        if ($path === $owned || str_starts_with($path, "$owned/")) {
            return true;
        }
    }

    return false;
}

function move_back(array $moved, string $from, string $to): void
{
    foreach (array_reverse($moved) as $rel) {
        if (file_exists("$from/$rel") || is_link("$from/$rel")) {
            rename_or_fail("$from/$rel", "$to/$rel");
        }
    }
}

function rename_or_fail(string $from, string $to): void
{
    if (!@rename($from, $to)) {
        throw new RuntimeException("cannot rename $from to $to");
    }
}

function copy_or_fail(string $from, string $to): void
{
    if (!@copy($from, $to)) {
        throw new RuntimeException("cannot copy $from to $to");
    }
}

function remove_tree(string $dir): void
{
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        $item->isDir() && !$item->isLink() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
    @rmdir($dir);
}

function reset_opcache(): void
{
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }
}

function save_state(string $file, array $state): void
{
    if (false === file_put_contents($file, json_encode($state, JSON_PRETTY_PRINT))) {
        throw new RuntimeException("cannot write $file");
    }
}

function load_state(string $file, string $expected): array
{
    $state = is_file($file) ? json_decode(file_get_contents($file), true) : null;
    if (($state['status'] ?? null) !== $expected) {
        throw new RuntimeException(sprintf('expected state "%s", found "%s"', $expected, $state['status'] ?? 'none'));
    }

    return $state;
}

function respond(int $code, string $message, array $data = [], bool $exit = true): void
{
    http_response_code($code);
    echo json_encode(['ok' => $code < 400, 'message' => $message] + $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
    if ($exit) {
        exit;
    }
}
