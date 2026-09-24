#!/usr/bin/env bash
# Builds the release zip that .github/workflows/deploy.yml uploads and
# deploy/release.php unpacks on the server. Run from the project root once
# the production vendor/, public/build/ and var/cache/prod/ are in place.
# Server-only files (.env, uploads, logs, .htaccess) are never packaged:
# release.php carries them over from the live tree.
set -euo pipefail

out=$1
rm -f "$out"
# Every path the repository tracks, so release.php can tell repo files the
# server still has from an older deploy (dropped) from server-only files
# (carried over).
git ls-files > .release-files
zip -qr "$out" . \
    -x '.git/*' '.github/*' '.idea/*' '.claude/*' 'node_modules/*' 'tests/*' 'docker/*' 'deploy/*' \
       'assets/*' 'docker-compose.yml' '.env' '.env.*' '.envrc' \
       '.phpunit/*' '.phpunit.result.cache' 'phpunit.xml*' \
       'public/uploads/*' 'var/log/*' 'var/cache/dev/*' 'var/cache/test/*' 'var/cache/*/pools/*'
