# Projet poteries-josette

Dernière mise à jour le 18/09/2026.

## Hébergement / production

- **Hébergeur** : OVH, base de données MySQL, administration via phpMyAdmin.
- Les identifiants de connexion, noms d'hôte et URL d'accès ne sont volontairement pas stockés dans ce skill versionné (donnée critique) — se référer à un gestionnaire de secrets ou demander à l'utilisateur si besoin.
- Schéma en base cohérent avec les entités Doctrine listées ci-dessous (pas de table dédiée à `Contact`, le formulaire de contact n'est probablement pas persisté en base).
- **Services OVH associés** : hébergement web (renouvellement manuel), nom de domaine (renouvellement manuel), zones DNS (renouvellement automatique), e-mails (aucun service actif constaté). Pas de dates précises stockées ici (vues lors d'un contrôle le 18/09/2026, potentiellement obsolètes/à revérifier directement sur l'espace client OVH plutôt que de se fier à ce résumé).

## Résumé

Site vitrine + petite boutique pour une potière artisanale : https://www.poterie-josette.com/. Le dépôt local est décrit comme "Redesign of an artisan potter's website" (README).

Remote GitHub : `git@github.com:marionemeb/poteries-josette.git` (SSH via l'alias dédié `github-marionemeb`)

## Stack technique

- **Backend** : Symfony 4.4, PHP ^7.1.3 — version probablement contrainte par l'offre d'hébergement mutualisé OVH, pas un choix délibéré du projet ; à vérifier/relâcher si l'hébergement change un jour
- **ORM** : Doctrine (avec migrations, `src/Migrations`)
- **Back-office** : EasyAdmin ^2.3
- **Front** : Webpack Encore + Sass, Bootstrap 4, jQuery, un peu de React (16) et animejs pour certaines parties interactives
- **PDF** : dompdf (génération de documents, ex. factures/mentions)
- **Emails** : Symfony Mailer + SwiftMailer bundle
- **Sécurité** : symfony/security-bundle + symfonycasts/reset-password-bundle
- **Qualité** : phpstan, phpunit (`phpstan.neon`, `phpunit.xml.dist`)

## Fonctionnalités du site (côté public)

Site vitrine à page unique par section, sans panier ni paiement en ligne (pas d'e-commerce malgré les noms de routes) :

- **`/` (Accueil)** — page d'accueil statique (`HomeController`).
- **`/articles`** — la vraie vitrine des poteries : liste tous les `Product` (créés depuis le back-office), pas de panier/achat en ligne, juste une galerie/catalogue de présentation.
- **`/shop`** (`ShopController`) — **piège de nommage** : ce n'est pas une boutique, c'est la page « Mon atelier » qui présente le lieu de travail de Josette (village d'Oingt), contenu statique.
- **`/work`** — page portfolio/travaux, statique (`WorkController`).
- **`/blog`** — « Coups de cœur » : liste filtrable par type (`BlogType`) via un formulaire de recherche GET.
- **`/recipes`** — recettes filtrables par catégorie (`RecipeCategory`), chaque recette peut être exportée en PDF via `/pdf/{id}` (généré avec Dompdf).
- **`/events`** — liste des événements (expositions, marchés).
- **`/contact`** — formulaire de contact ; à la soumission, envoie un email via le service `Notification` (pas de stockage visible en base, cohérent avec l'absence de table `contact`).
- **`/legalMentions`** — mentions légales.
- **`/login`, `/logout`, `/reset-password/*`** — authentification réservée à l'administration du site (pas d'inscription publique : un seul rôle utilisateur exploité, `ROLE_USER`, pas de formulaire de création de compte visible).

## Back-office (EasyAdmin, `config/packages/easy_admin.yaml`)

Interface d'administration ("Les Poteries de Josette") où Josette gère elle-même le contenu, avec upload d'images (VichUploaderBundle) :
- **Product** ("Mes poteries") — les pièces présentées sur `/articles`
- **Event** ("Mes évènements")
- **Blog** ("Coups de cœur") + **BlogType** ("Types de coups de cœur")
- **Recipe** ("Mes recettes") + **RecipeCategory** ("Catégories de recettes")

## Architecture — détail technique

**Entités** (`src/Entity`) : `Blog`, `BlogType`, `Contact`, `Event`, `Product`, `Recipe`, `RecipeCategory`, `User`, `ResetPasswordRequest`.

**Contrôleurs** (`src/Controller`) : `HomeController`, `ArticlesController`, `BlogController`, `ContactController`, `EventsController`, `LegalMentionsController`, `PdfController`, `RecipesController`, `SecurityController`, `ResetPasswordController`, `ShopController`, `WorkController` — voir "Fonctionnalités" ci-dessus pour ce que fait chacun.

Autres dossiers `src/` : `Form` (formulaires Symfony, dont les formulaires de recherche `SearchType`/`SearchRecipeType`), `Notification` (envoi d'email pour le contact), `Security`, `Repository`, `Migrations`, `Kernel.php`.

## Comptes et accès — configuration dédiée à ce repo

Mise en place le 18/09/2026 :

- **Git** : identité locale scopée sur ce repo (`user.name`/`user.email` en config `--local`, distincts de la config globale), remote `origin` repointé vers l'alias SSH `github-marionemeb` (force la clé `~/.ssh/id_ed25519_marionemeb`) pour garantir que push/pull passent toujours par le compte GitHub `marionemeb`.
- **Claude Code** : pas de scoping natif par projet côté Claude Code (credentials globales à la machine dans `~/.claude/.credentials.json`). Contournement mis en place : `.envrc` à la racine du repo (exclu du repo via `.git/info/exclude`, jamais commité) qui exporte `CLAUDE_CONFIG_DIR=~/.claude-profiles/poteries-josette-gmail`, activé via `direnv`. Le hook direnv dans `~/.zshrc` a été corrigé au passage (il était câblé pour bash au lieu de zsh).
- Le compte associé à ce repo (Git et Claude Code) est `marion.emeric@gmail.com` / compte GitHub `marionemeb`.

## Ce skill

Ce skill (`.claude/skills/poteries-josette/`) est versionné avec le repo — contrairement à un skill de compte Claude.ai classique, il n'est disponible que dans les sessions Claude Code ouvertes sur ce dépôt, et se met à jour comme n'importe quel fichier du projet (édition directe + commit).

## Historique récent (avant la création de ce skill)

- Ajout des mentions légales (`LegalMentionsController`, commit "Add legal mentions" + "update legal mentions")
- Fix d'un bug spécifique à l'environnement de prod ("fix bug for prod env")

## État des lieux / TODO

- [ ] Ajouter Instagram (lien/flux/intégration — à préciser : simple lien vers le compte, ou affichage des posts sur le site)
- [ ] Reprendre l'UI/UX (voir `assets/design-poteries-josette.md` pour l'état actuel du design avant toute refonte)
- [ ] Vérifier la responsivité du site sur différents supports (PC, tablette, mobile)
- [ ] Ajouter un bouton "remonter en haut" sur les pages
- [ ] Reprendre les images de mauvaise qualité
- [ ] Voir avec Josette pour supprimer la page "événements"
- [ ] Idem : voir avec Josette pour les pages "recettes", "coups de cœur" et "contact" (+ vérifier si la partie back-office correspondante serait aussi à retirer)
- [ ] Revoir tout le plan du site (sitemap/architecture des pages) — englobe probablement les décisions au cas par cas ci-dessus sur événements/recettes/coups de cœur/contact
- [ ] Ajouter des tests unitaires / fonctionnels (voir aussi "Pistes d'amélioration" ci-dessous : `tests/` est actuellement vide de tout test réel)
- [ ] Ajouter un CI/CD (voir aussi "Pistes d'amélioration" ci-dessous : pas de `.github/workflows` actuellement, déploiement manuel)
- [ ] Revoir le footer
- [ ] Revoir les mentions légales
- [ ] Automatiser le déploiement vers OVH (actuellement manuel)
- [ ] Mettre à jour `composer.lock` et `package-lock.json`

## Pistes d'amélioration identifiées (18/09/2026)

**Priorité — sécurité/risque** : PHP ^7.1.3 et Symfony 4.4 sont tous les deux hors support depuis longtemps (PHP 7.1 EOL fin 2019, Symfony 4.4 EOL nov. 2023), sur un site public avec un back-office/login exposé. À traiter avant le reste. Compromis : upgrader est un chantier, potentiellement bloqué par l'offre PHP d'OVH (voir "Stack technique" ci-dessus) — vérifier d'abord si un hébergement OVH plus récent est possible sans tout migrer.

**Technique**
- Aucun test réel n'existe (`tests/` ne contient qu'un `bootstrap.php`, malgré `phpunit.xml.dist` configuré) — zéro filet de sécurité pour changer du code sans casser le site.
- Pas de CI/CD (pas de `.github/workflows`) — chaque déploiement se fait manuellement.
- EasyAdmin 2.3 est aussi une version ancienne (la 4 existe, mais liée à la montée de version Symfony).

**Fonctionnel**
- Naming trompeur de la route `/shop` (voir "Fonctionnalités" ci-dessus) — à corriger si ça gêne, ou au moins renommer en interne.
- Pas de RGPD/bandeau cookies visible malgré un formulaire de contact qui envoie un email — à vérifier selon ce qui est réellement stocké.
- Pas de vrai panier/paiement — si Josette veut un jour vendre en ligne plutôt qu'exposer juste un catalogue, c'est un vrai chantier fonctionnel.

**Autre**
- Pas de sauvegarde de la BDD OVH confirmée — à vérifier que l'hébergeur en fait une automatiquement.
- Pas de monitoring/alerting (site down, erreurs 500) en place.

