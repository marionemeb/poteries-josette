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

## Qualité

```bash
vendor/bin/phpstan analyse
vendor/bin/phpunit
```
