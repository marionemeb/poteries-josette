# Les Poteries de Josette

Site vitrine pour [poterie-josette.com](https://www.poterie-josette.com/), l'atelier d'une potière artisanale installée à Oingt (Beaujolais). Redesign du site existant.

## Fonctionnalités

Site de présentation (pas d'e-commerce, pas de panier/paiement en ligne) :

- **Catalogue de poteries** (`/articles`) — galerie des pièces créées par la potière
- **Mon atelier** (`/shop`) — présentation du lieu de travail à Oingt
- **Travaux / portfolio** (`/work`)
- **Coups de cœur** (`/blog`) — recommandations filtrables par type, masqué dans la nav/footer s'il n'y a aucun article
- **Recettes** (`/recipes`) — filtrables par catégorie, exportables en PDF, masqué dans la nav/footer s'il n'y a aucune recette
- **Événements** (`/events`) — expositions, marchés, masqué dans la nav/footer s'il n'y a aucun événement à venir (les événements passés ne s'affichent pas non plus)
- **Mentions légales** (`/legalMentions`)
- **Back-office** (EasyAdmin) — administration du contenu (poteries, événements, coups de cœur, recettes et leurs catégories/types) avec upload d'images

## Stack technique

- **Backend** : Symfony 4.4 (PHP ^7.1.3), Doctrine ORM
- **Back-office** : EasyAdmin 2.3
- **Front** : Webpack Encore, Sass, Bootstrap 4, jQuery, React (composants ponctuels)
- **PDF** : Dompdf
- **Email** : Symfony Mailer / SwiftMailer

## Installation

Prérequis : PHP ^7.1.3 avec les extensions `ctype` et `iconv`, Composer, Node.js/Yarn, une base MySQL.

```bash
composer install
yarn install

cp .env.dist .env   # puis ajuster DATABASE_URL/MAILER_DSN/APP_SECRET si besoin
php bin/console doctrine:migrations:migrate

yarn build           # build de production des assets
```

## Développement

Sur une machine sans PHP 7.x/Node 12 natifs, `bin/dev-setup.sh` enchaîne tout ce qu'il faut (Docker, dépendances, base de dev + schéma, données factices, assets front) en une seule commande, sûr à relancer plusieurs fois :

```bash
bin/dev-setup.sh

# Puis pour lancer le serveur :
docker compose run --rm -p 8000:8000 -e APP_ENV=dev php php -S 0.0.0.0:8000 -t public
# et ouvrir http://localhost:8000
```

Avec les outils installés nativement :

```bash
symfony server:start     # ou php -S 127.0.0.1:8000 -t public
yarn watch                # rebuild les assets à chaque changement
```

## Tests

La machine de dev n'a pas forcément PHP 7.x installé : un environnement Docker (PHP 7.4 + MySQL) est fourni pour lancer les tests de façon reproductible, proche de la prod (OVH tourne en PHP 7.3).

```bash
docker compose build php
docker compose run --rm php composer install

# La base de test doit exister et être à jour avant de lancer les tests
docker compose run --rm -e APP_ENV=test php php bin/console doctrine:schema:update --force

# PHPUnit 7.5 (composer.lock étant lui-même en Composer 1, la
# récupération dynamique via symfony/phpunit-bridge ne fonctionne
# plus depuis l'arrêt du support Composer 1 par Packagist — un phar
# autonome est utilisé à la place, voir .phpunit/phpunit.phar)
docker compose run --rm php php .phpunit/phpunit.phar
```

Le fichier `.env` (non commité, `cp .env.dist .env` — voir "Installation" ci-dessus) doit exister pour que le kernel démarre ; `.env.test` (commité) surcharge `DATABASE_URL` pour pointer vers le service `db` du `docker-compose.yml`. La CI (`.github/workflows/tests.yml`) fait ce `cp` automatiquement.

**Attention** : `doctrine:schema:update` sert ici uniquement à préparer la base de **test**, isolée dans le conteneur Docker — ne jamais l'utiliser sur la base de production. `src/Migrations/` n'est de toute façon plus à jour avec le mapping actuel (voir le skill du projet), donc `doctrine:migrations:migrate` seul ne suffit pas pour retrouver un schéma de test cohérent avec le code.

## Qualité

```bash
docker compose run --rm php vendor/bin/phpstan analyse
```

## Déploiement

`.github/workflows/deploy.yml` déploie sur l'hébergement OVH par FTP — build des assets (Node 12, requis pour `node-sass`) + `composer install --no-dev`, puis synchronisation FTP en excluant les fichiers de dev (`.git`, `.github`, `tests/`, `docker/`, `.env*`...).

**Déclenchement manuel uniquement** (`workflow_dispatch`, onglet Actions → "Deploy to OVH" → "Run workflow") — c'est le site de production réel, pas de déploiement automatique à chaque push pour l'instant.

Secrets GitHub à configurer une fois (Settings → Secrets and variables → Actions) avant de pouvoir lancer le workflow :
- `OVH_FTP_HOST`
- `OVH_FTP_USERNAME`
- `OVH_FTP_PASSWORD`
- `OVH_FTP_REMOTE_DIR` (dossier racine du site sur l'hébergement, ex. `www/`)
