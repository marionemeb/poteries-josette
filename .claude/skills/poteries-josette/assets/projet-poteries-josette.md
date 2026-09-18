# Projet poteries-josette

Dernière mise à jour le 18/09/2026.

## Hébergement / production

- **Hébergeur** : OVH, base de données MySQL, administration via phpMyAdmin.
- Les identifiants de connexion, noms d'hôte et URL d'accès ne sont volontairement pas stockés dans ce skill versionné (donnée critique) — se référer à un gestionnaire de secrets ou demander à l'utilisateur si besoin.
- Schéma en base cohérent avec les entités Doctrine listées ci-dessous (pas de table dédiée à `Contact`, le formulaire de contact n'est probablement pas persisté en base).
- **Services OVH associés** : hébergement web (renouvellement manuel), nom de domaine (renouvellement manuel), zones DNS (renouvellement automatique), e-mails (aucun service actif constaté). Pas de dates précises stockées ici (vues lors d'un contrôle le 18/09/2026, potentiellement obsolètes/à revérifier directement sur l'espace client OVH plutôt que de se fier à ce résumé).
- **Abonnement hébergement** (vérifié le 18/09/2026) : offre PERSO, expire le 30/03/2027, compte créé le 22/07/2009. Quota bases de données : 1/5 utilisées.
- **Multisite** : 3 entrées configurées — `poterie-josette.com` et `www.poterie-josette.com` (dossier racine `www/public`, DNS actif A/AAAA) + `poteriej.cluster014.ovh.net` (sous-domaine technique OVH, dossier racine `www`). Git/Logs séparés/Firewall désactivés sur les trois.

## Résumé

Site vitrine + petite boutique pour une potière artisanale : https://www.poterie-josette.com/. Le dépôt local est décrit comme "Redesign of an artisan potter's website" (README).

Remote GitHub : `git@github.com:marionemeb/poteries-josette.git` (SSH via l'alias dédié `github-marionemeb`)

## Stack technique

- **Backend** : Symfony 4.4. `composer.json` exige `PHP ^7.1.3` (minimum), mais la **version PHP réellement déployée sur OVH est 7.3** (vérifiée le 18/09/2026 dans l'espace client OVH, marquée d'un avertissement — probablement signalée EOL par OVH lui-même). Offre d'hébergement : **PERSO**. D'après la doc officielle OVH, la version PHP n'est PAS limitée par l'offre (Perso/Pro/Performance) : OVH propose PHP 5.4 à 8.5 sur tout hébergement mutualisé, configurable par site (espace client → Hébergements → Multisite → domaine → Configuration → "Version PHP globale"). PHP 7.3 est donc probablement un réglage jamais mis à jour, pas une contrainte d'offre — mais Symfony 4.4 lui-même ne supporte officiellement que jusqu'à PHP 7.4, donc une vraie modernisation nécessite aussi de monter Symfony, pas seulement de changer la version PHP dans le manager OVH.
- **ORM** : Doctrine (avec migrations, `src/Migrations`)
- **Back-office** : EasyAdmin ^2.3
- **Front** : Webpack Encore + Sass, Bootstrap 4, jQuery, un peu de React (16) et animejs pour certaines parties interactives. **`yarn` n'est pas installé sur cette machine** (README à corriger un jour) — utiliser `npm` à la place (`npm install`, `npx encore ...`). `yarn.lock` et `package-lock.json` coexistent dans le repo alors que seul npm est utilisable ici ; une commande npm a tendance à modifier `yarn.lock` en plus (changements d'URL de registry uniquement, pas de vraies versions) — vérifier `git diff yarn.lock` et le `checkout` si ce n'est pas voulu.
- **`node-sass` 4.x cassé sur Node 22** (constaté le 18/09/2026) : `encore dev`/`build` échouent (`Unsupported runtime`, vérifié aussi en 404 sur le binaire précompilé GitHub — pas juste un souci de config locale). Fonctionne en revanche très bien sur **Node 12** (utilisé par le workflow de déploiement, voir plus bas) — donc pas bloquant pour le déploiement, seulement pour builder les assets directement sur cette machine de dev. Attention : Node 12/14 embarquent npm 6, qui ne sait pas lire notre `package-lock.json` (format `lockfileVersion 3`, généré par npm 11) — `npm install -g npm@7` avant `npm ci` réglé ça, déjà fait dans le workflow. Migration vers `sass` (dart-sass) souhaitable à terme pour ne plus dépendre d'une vieille version de Node — voir aussi le point UI/UX du TODO, ça pourrait se faire en même temps.
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
- **Email des commits** : `marion.emeric@laposte.net` (config locale `user.email` de ce repo). **Important** : la clé SSH `github-marionemeb` ne contrôle que le droit de *push* — GitHub attribue un commit à un compte en fonction de l'email de l'auteur (`user.email` git), pas de la clé SSH utilisée. Le 18/09/2026, deux commits faits avec `marion.emeric@gmail.com` (email de session Claude Code, mais vérifié sur un *autre* compte GitHub perso, `marion-emeric`) ont été attribués à ce mauvais compte dans le graphe de contributions GitHub — corrigé en réécrivant ces 2 commits (author réécrit + `push --force-with-lease`, nécessitant une désactivation temporaire de la protection anti-force-push sur `master`). Toujours utiliser `marion.emeric@laposte.net` pour les commits sur ce repo, jamais l'email de session par défaut.

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
- [x] Ajouter des tests unitaires / fonctionnels — 19 tests passants au 18/09/2026 (`tests/Entity/UserTest.php`, `tests/Controller/PublicPagesTest.php`, `EventsControllerTest`, `ArticlesControllerTest`, `BlogControllerTest`, `RecipesControllerTest`, base commune `tests/DatabaseWebTestCase.php` pour les fixtures faites main, pas de bundle de fixtures installé). Bug réel trouvé et corrigé au passage : `PdfController` utilisait `$dompdf->stream()` (envoie les headers/le contenu directement, incompatible avec un `Response` Symfony) au lieu de `$dompdf->output()` — corrigé, `/pdf/{id}` est maintenant testé aussi. Reste à faire : formulaires de recherche/filtre (blog/recipes), soumission du formulaire de contact, back-office EasyAdmin.
- [x] Ajouter un CI/CD — `.github/workflows/tests.yml` ajouté le 18/09/2026 : lance la suite de tests (Docker PHP 7.4 + MySQL) à chaque push/PR sur `master`. Ne couvre que les tests, pas le déploiement (toujours manuel, voir plus bas).
- [ ] Revoir le footer
- [ ] Revoir les mentions légales
- [~] Automatiser le déploiement vers OVH — `.github/workflows/deploy.yml` ajouté le 18/09/2026, déclenchement **manuel uniquement** (`workflow_dispatch`, décision volontaire vu qu'il s'agit du site de prod réel — pas de déploiement automatique à chaque push pour l'instant). Reproduit le process manuel actuel (FTP) : build assets (Node 12, requis pour `node-sass`, voir "Stack technique") + `composer install --no-dev`, puis synchronisation FTP en excluant `.git`, `.github`, `tests/`, `docker/`, `.env*`. **Pas encore testé en conditions réelles** : nécessite que l'utilisateur ajoute les secrets GitHub `OVH_FTP_HOST`, `OVH_FTP_USERNAME`, `OVH_FTP_PASSWORD`, `OVH_FTP_REMOTE_DIR` (jamais vus par Claude) avant de pouvoir lancer le workflow.
- [x] Mettre à jour `package-lock.json` — fait le 18/09/2026 via `npm update` (respecte les bornes semver de `package.json`, pas de montée majeure). Découverte au passage : le build front (`encore dev`) était déjà cassé sur cette machine (Node 22) *avant* la mise à jour, à cause d'une incompatibilité OpenSSL 3 avec l'ancien webpack 4 — la mise à jour n'a rien cassé de plus, elle a même réglé ce point-là. Reste un blocage résiduel : `node-sass` 4.x (obsolète, abandonné) n'a aucun binaire précompilé pour Node 22 (`Unsupported runtime`) — nécessiterait de migrer vers `sass` (dart-sass), un vrai chantier front séparé, pas juste une mise à jour de lockfile.
- [ ] Mettre à jour `composer.lock` — **bloqué**, voir "Pistes d'amélioration" ci-dessous : équivaut en fait au chantier de modernisation Symfony/PHP, pas une mise à jour de routine.
- [ ] Mettre en place Dependabot — côté npm ça devrait fonctionner normalement ; côté composer, à vérifier une fois le blocage Composer 1/Packagist ci-dessus résolu (Dependabot a sa propre logique de résolution, pas forcément impactée, mais pas garanti tant que ce n'est pas testé).

## Pistes d'amélioration identifiées (18/09/2026)

**Priorité — sécurité/risque** : PHP ^7.1.3 et Symfony 4.4 sont tous les deux hors support depuis longtemps (PHP 7.1 EOL fin 2019, Symfony 4.4 EOL nov. 2023), sur un site public avec un back-office/login exposé. À traiter avant le reste. Bonne nouvelle : ce n'est PAS bloqué par l'offre OVH (PHP jusqu'à 8.5 disponible sur toute offre mutualisée, voir "Stack technique" ci-dessus) — le vrai chantier est de monter Symfony (4.4 ne supporte officiellement que jusqu'à PHP 7.4) et ses dépendances avant de pouvoir changer la version PHP côté OVH.

**`composer update`/`require` définitivement impossible en l'état** (vérifié le 18/09/2026) : l'ancien protocole Composer 1 (`repo.packagist.org/p/%package%.json`) renvoie désormais une **403 Forbidden** — pas juste un avertissement de dépréciation, un vrai blocage. `symfony/flex` (v1.6.3, verrouillé dans le lock) appelle en plus `flex.symfony.com`, un nom de domaine qui **ne résout même plus du tout** (service disparu). Résultat : le seul moyen de faire évoluer `composer.lock` est de migrer vers Composer 2, ce qui implique de remplacer `symfony/flex` et `ocramius/package-versions` (verrouillés sur des versions qui exigent l'API de plugin Composer 1) — et vu l'ampleur des écarts de version déjà constatés (ex. `doctrine/doctrine-bundle` verrouillé en 2.0.8, la 3.3.2 actuelle exige PHP 8.4), cette migration ne peut pas être découplée de la montée Symfony/PHP ci-dessus : **c'est le même chantier**, pas une tâche de routine séparée.

**Technique**
- Premiers tests ajoutés le 18/09/2026 (voir TODO ci-dessus) — encore un filet de sécurité limité, à étoffer.
- `phpstan` (config par défaut, niveau 0) remonte 78 erreurs préexistantes (typehints manquants surtout) — pas corrigées, hors scope de l'ajout des tests ; à traiter séparément si on veut un vrai niveau de qualité statique.
- Pas de CI/CD (pas de `.github/workflows`) — chaque déploiement se fait manuellement.
- EasyAdmin 2.3 est aussi une version ancienne (la 4 existe, mais liée à la montée de version Symfony).
- **Environnement de test** : la machine de dev n'a que PHP 8.1 avec très peu d'extensions (pas de mbstring/xml/pdo_mysql...) — inadapté au projet (PHP ^7.1.3). Un environnement Docker a été mis en place (`docker-compose.yml` + `docker/php/Dockerfile`, image `php:7.4-cli`) : `docker compose run --rm php <commande>`. Points à connaître :
  - Le `composer.lock` du projet a été généré avec **Composer 1** (symfony/flex, ocramius/package-versions pinnés sur des versions qui exigent l'API de plugin Composer 1). Le Dockerfile installe donc `composer:1`, pas `composer:2`.
  - `composer install` fonctionne (résolution depuis le lock + cache), mais **Packagist a coupé le support de Composer 1 en septembre 2025** : toute résolution *live* (ex. `composer update`, ou le mécanisme `vendor/bin/simple-phpunit` de symfony/phpunit-bridge qui télécharge PHPUnit à la volée) échoue désormais. Contournement : un phar PHPUnit 7.5 autonome est téléchargé directement (`.phpunit/phpunit.phar`, dossier déjà ignoré par git) et lancé via `docker compose run --rm php php .phpunit/phpunit.phar`.
  - Implication plus large : moderniser ce projet (Symfony 5+, PHP 7.4/8+) réglera aussi ce problème de Composer 1, en plus du reste (voir priorité sécurité/risque ci-dessus).
  - `config/packages/test/webpack_encore.yaml` : `strict_mode` activé à `false` (était commenté) pour que les tests fonctionnels n'échouent pas faute d'assets Webpack buildés.
  - `.env` n'est pas commité (gitignoré) et Symfony a besoin qu'il existe pour démarrer. Un `.env.dist` (template, valeurs non sensibles) est committé à la place — `cp .env.dist .env` avant de travailler (README) ou en CI.
  - Le service `db` du `docker-compose.yml` a un healthcheck MySQL — **attention** : `mysqladmin ping` sans `--protocol=tcp` peut répondre "healthy" pendant la phase d'initialisation interne de mysqld (réseau désactivé, ping local via socket réussit), avant que le serveur ne soit réellement joignable depuis le conteneur `php` → forcer `--protocol=tcp` dans le test du healthcheck.
  - Les fichiers créés dans le volume monté (ex. `vendor/`) appartiennent à `root` sur l'hôte (le conteneur tourne en root) — `rm -rf vendor` échoue côté hôte, il faut passer par `docker compose run --rm php rm -rf vendor`.
  - **Piège côté npm sur cette machine précise** : `~/.npmrc` (config globale, héritée d'un autre projet/emploi) contient `ignore-scripts=true` et pointe le registre par défaut vers un registre privé (GCP Artifact Registry) sans rapport avec ce projet. Un `.npmrc` propre au repo (committé, `ignore-scripts=false` + `registry=https://registry.npmjs.org/`) corrige ça pour ce projet précisément, sans toucher à la config globale de la machine (utile pour l'autre emploi). Effet de bord attendu : `npm install` échoue maintenant **franchement** sur Node 22 (node-sass tente vraiment de compiler et échoue, `node-gyp` en erreur), au lieu d'échouer silencieusement plus tard au moment du build — utiliser Node 12 (`nvm use 12`, puis `npm install -g npm@7` car Node 12/14 embarquent un npm 6 qui ne lit pas notre lockfile v3) pour tout travail npm local sur cette machine, comme le fait déjà le workflow de déploiement.
- **CI** : `.github/workflows/tests.yml` reproduit exactement cette séquence (build image, `composer install`, schéma de test, téléchargement du phar PHPUnit, exécution) à chaque push/PR sur `master`. `phpstan` n'est volontairement pas dans la CI pour l'instant (78 erreurs préexistantes le feraient échouer dès le premier run).

**Fonctionnel**
- Naming trompeur de la route `/shop` (voir "Fonctionnalités" ci-dessus) — à corriger si ça gêne, ou au moins renommer en interne.
- Pas de RGPD/bandeau cookies visible malgré un formulaire de contact qui envoie un email — à vérifier selon ce qui est réellement stocké.
- Pas de vrai panier/paiement — si Josette veut un jour vendre en ligne plutôt qu'exposer juste un catalogue, c'est un vrai chantier fonctionnel.

**Autre**
- Pas de sauvegarde de la BDD OVH confirmée — à vérifier que l'hébergeur en fait une automatiquement.
- Pas de monitoring/alerting (site down, erreurs 500) en place.
- **Migrations en retard sur le schéma réel** (découvert le 18/09/2026 en montant une BDD de test à partir de `src/Migrations/`) : `doctrine:schema:validate` échoue — colonnes manquantes/à renommer sur `event`/`recipe`/`product` (ex. `event.name` devrait être `event.title`, `updated_at` absent partout, `recipe.category_id` absent), et les tables `user`, `recipe_category`, `reset_password_request` n'ont aucune migration alors qu'elles existent en prod (vues dans phpMyAdmin). Schéma prod probablement modifié à la main ou via `schema:update` sans générer les migrations correspondantes — risque pour toute reprise après sinistre à partir des migrations seules. À régulariser : générer les migrations manquantes (`doctrine:migrations:diff`) en comparant au schéma réel de prod, pas juste à celui obtenu en local.

