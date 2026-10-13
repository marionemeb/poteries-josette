<?php

namespace App\Tests\Assets;

use PHPUnit\Framework\TestCase;

/**
 * Every photo used through @include photo-background() or picture.img()
 * must exist in both formats: a missing .webp shows an empty background in
 * recent browsers, a missing .jpg in older ones.
 */
class PhotoFilesTest extends TestCase
{
    private const ROOT = __DIR__.'/../..';

    public function testEveryPhotoExistsAsWebpAndJpeg(): void
    {
        $names = [];
        foreach ($this->files(self::ROOT.'/assets/scss', 'scss') as $file) {
            preg_match_all('/@include photo-background\(.*,\s*"([\w-]+)"\)/', file_get_contents($file), $m);
            $names = array_merge($names, $m[1]);
        }
        foreach ($this->files(self::ROOT.'/templates', 'twig') as $file) {
            preg_match_all("/picture\.img\('([\w-]+)'/", file_get_contents($file), $m);
            $names = array_merge($names, $m[1]);
        }
        $names = array_unique($names);

        $this->assertNotEmpty($names);
        foreach ($names as $name) {
            $this->assertFileExists(self::ROOT."/public/img/$name.webp");
            $this->assertFileExists(self::ROOT."/public/img/$name.jpg");
        }
    }

    /** @return string[] */
    private function files(string $dir, string $extension): array
    {
        $found = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
            if ($file->getExtension() === $extension) {
                $found[] = $file->getPathname();
            }
        }

        return $found;
    }
}
