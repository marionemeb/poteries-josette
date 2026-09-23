#!/usr/bin/env bash
# Prépare l'environnement de dev local en une seule commande : .env, image
# Docker, dépendances PHP, base de dev + schéma, données factices, assets
# front. Sûr à relancer plusieurs fois (idempotent).
set -euo pipefail
cd "$(dirname "$0")/.."

echo "==> .env"
if [ ! -f .env ]; then
    cp .env.dist .env
    echo "    créé depuis .env.dist"
else
    echo "    déjà présent, inchangé"
fi

echo "==> Image Docker PHP"
docker compose build php

echo "==> Dépendances PHP"
docker compose run --rm php composer install

echo "==> Base de données de dev (josette_dev)"
# Une base créée avant le passage aux migrations (via schema:update) n'a pas
# de table migration_versions : migrate échouerait sur des tables existantes.
# Elle ne contient que des données factices, on la recrée.
if docker compose exec -T db mysql -uroot -N -e "SHOW TABLES FROM josette_dev" 2>/dev/null | grep -qx user \
    && ! docker compose exec -T db mysql -uroot -N -e "SHOW TABLES FROM josette_dev" | grep -qx migration_versions; then
    echo "    ancienne base sans migrations, recréée"
    docker compose exec -T db mysql -uroot -e "DROP DATABASE josette_dev;"
fi
docker compose exec -T db mysql -uroot -e \
    "CREATE DATABASE IF NOT EXISTS josette_dev; GRANT ALL PRIVILEGES ON josette_dev.* TO 'josette'@'%'; FLUSH PRIVILEGES;"
docker compose run --rm -e APP_ENV=dev php php bin/console doctrine:migrations:migrate --no-interaction

echo "==> Données factices (no-op si déjà présentes)"
docker compose run --rm -e APP_ENV=dev php php bin/console app:load-fake-data

echo "==> Assets front (Node 12 requis pour node-sass, voir le skill du projet)"
if [ -s "$HOME/.nvm/nvm.sh" ]; then
    # shellcheck source=/dev/null
    . "$HOME/.nvm/nvm.sh"
    nvm install 12 >/dev/null
    nvm use 12 >/dev/null
    npm install -g npm@7 >/dev/null
    npm ci
    npm run build
else
    echo "    nvm introuvable — installe Node 12 puis lance :"
    echo "    npm install -g npm@7 && npm ci && npm run build"
fi

echo
echo "==> Terminé. Pour lancer le serveur :"
echo "    docker compose run --rm -p 8000:8000 -e APP_ENV=dev php php -S 0.0.0.0:8000 -t public"
echo "    puis ouvrir http://localhost:8000"
