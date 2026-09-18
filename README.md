# Les Poteries de Josette

Site vitrine pour [poterie-josette.com](https://www.poterie-josette.com/), l'atelier d'une potière artisanale installée à Oingt (Beaujolais). Redesign du site existant.

## Fonctionnalités

Site de présentation (pas d'e-commerce, pas de panier/paiement en ligne) :

- **Catalogue de poteries** (`/articles`) — galerie des pièces créées par la potière
- **Mon atelier** (`/shop`) — présentation du lieu de travail à Oingt
- **Travaux / portfolio** (`/work`)
- **Coups de cœur** (`/blog`) — recommandations filtrables par type
- **Recettes** (`/recipes`) — filtrables par catégorie, exportables en PDF
- **Événements** (`/events`) — expositions, marchés
- **Contact** (`/contact`) — formulaire avec envoi d'email
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

cp .env .env.local   # renseigner les variables (DATABASE_URL, MAILER_DSN, APP_SECRET...)
php bin/console doctrine:migrations:migrate

yarn build           # build de production des assets
```

## Développement

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

Le fichier `.env` (non commité) doit exister pour que le kernel démarre — voir "Installation" ci-dessus ; `.env.test` (commité) surcharge `DATABASE_URL` pour pointer vers le service `db` du `docker-compose.yml`.

**Attention** : `doctrine:schema:update` sert ici uniquement à préparer la base de **test**, isolée dans le conteneur Docker — ne jamais l'utiliser sur la base de production. `src/Migrations/` n'est de toute façon plus à jour avec le mapping actuel (voir le skill du projet), donc `doctrine:migrations:migrate` seul ne suffit pas pour retrouver un schéma de test cohérent avec le code.

## Qualité

```bash
docker compose run --rm php vendor/bin/phpstan analyse
```
