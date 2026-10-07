# Design / identité visuelle — poteries-josette

**Refonte complète le 07/10/2026** (branche `design-refresh`), à partir d'une maquette Claude Design validée par l'utilisatrice : https://claude.ai/artifact/VzDL94r2gjoGM226dfcSvm (13 écrans : 7 pages ordinateur, 5 écrans mobile, pied de page). Ce document décrit l'état après cette refonte ; l'historique des retouches précédentes (septembre 2026) est résumé en bas.

## Typographie

- **Titres** : `Courgette` (manuscrite, Google Fonts) — le cachet « fait main ». **Corps** : `Open Sans` 400/600/700.
- Polices chargées par `<link>` dans `base.html.twig` (avec `preconnect`), et non plus par `@import` dans le SCSS.
- **Icônes** : SVG inline via la macro `templates/_icons.html.twig` (`{% import '_icons.html.twig' as icon %}` puis `{{ icon.svg('phone', 20) }}`). **Le kit Font Awesome a été retiré** (une dépendance externe de moins). Pour une nouvelle icône, ajouter son tracé dans la macro.

## Couleurs (variables dans `assets/scss/_variables.scss`)

- `$ink` `#222424` (footer, boutons pleins, pastille active), `$ink-deep` `#1d2124` (menu mobile, bandeau mentions légales), `$ink-soft` `#3a3d3d` (survols).
- `$paper` `#faf7f2` (fond crème des pages), `$paper-deep` `#f1ebe2` (sections alternées, encadrés), `$card` blanc.
- `$text` `#2d2a26`, `$muted` `#6f6a63` (textes secondaires, contraste OK sur crème).
- Pastilles d'émaux (page Argile) : miel `#9a5b2a`, jaune `#d6a42b`, vert `#4f7a3a`.
- **Toujours pas d'orange** (choix de l'utilisatrice, 23/09/2026).

## Organisation du SCSS

Un seul fichier CSS (`app.scss`, entrée Encore `app`) qui importe `_variables`, Bootstrap 4 puis des partiels :
`components/` (`_base`, `_buttons`, `_header`, `_page`, `_cards`, `_category-filter`, `_lightbox`, `_visit`, `_footer`) et `pages/` (`_home`, `_text-pages`, `_events`, `_recipes`, `_blog`, `_login`, `_error`). Avant : 6 entrées Encore qui réimportaient chacune `app.scss`, donc Bootstrap chargé 6 fois sur chaque page. Points de rupture via les mixins Bootstrap (`media-breakpoint-down(xs|sm|md|lg)`).

## Structure des pages

- **En-tête** (`_navbar.html.twig`) : barre fixe, transparente sur la photo puis sombre et floutée dès qu'on défile (`.is-scrolled`, `app.js`). Nom du site à gauche (masqué en haut de l'accueil, où le grand titre suffit), liens à droite, page courante soulignée (`aria-current`). **Menu burger sous 1200px** : menu plein écran (Courgette 30px), croix dessinée en CSS, Échap pour fermer. Lien d'évitement « Aller au contenu » (`#contenu`).
- **Pages intérieures** : bandeau photo (`.page-hero` + modificateur par page, ex. `.page-hero--articles`) avec titre Courgette blanc (`.page-title`, 64px / 40px mobile) et chapeau (`.page-lead`), puis contenu sur fond crème (`.page-content`) qui remonte de 90px sur la photo. **Changement assumé** par rapport à l'ancien principe « photo pleine page derrière tout le contenu » (validé avec la maquette). Les textes longs (Argile, Atelier, mentions légales) sont dans une feuille blanche `.paper`.
- **Accueil** : grand visuel plein écran (coquelicots) avec « Oingt · Beaujolais », titre, chapeau, boutons « Voir les poteries » / « Visiter l'atelier » ; présentation de Josette + 3 atouts (terre vernissée, four, lave-vaisselle) ; 3 tuiles photo vers Poteries / Argile / Atelier ; **bandeau « Prochain évènement »** (seulement s'il y en a un, `HomeController` → `EventRepository::findUpcoming()`, protégé par try/catch) ; bloc « Venir à l'atelier ».
- **Poteries** : grille de cartes (3 colonnes, 2 en tablette, **2 sur téléphone** avec photo carrée et titre seul). Clic sur une photo → **visionneuse plein écran** avec titre, compteur, précédent/suivant, flèches clavier, balayage au doigt, Échap ou clic sur le fond pour fermer (`app.js`). Une carte dont la photo ne charge pas disparaît.
- **Argile et émaux** : texte de Josette découpé en sections (intro + photo, pastilles des 3 couleurs, note « engobe », étapes 1-2-3, encadré « Bon à savoir », formes, pièces décoratives, signature).
- **Atelier** : citation sur Oingt + 2 photos, carte sombre « Venir à l'atelier » (partial `_visit.html.twig`, partagé avec l'accueil : adresse, téléphone, e-mail, boutons Itinéraire / Appeler).
- **Évènements** : cartes photo + pastille de date en français (« 21 oct. ») + dates en toutes lettres (« Du 21 au 23 octobre 2026 », macros `templates/events/_dates.html.twig`, sans extension intl). Photo visible aussi sur mobile (au-dessus). Carte cliquable si un lien est renseigné (nouvel onglet).
- **Recettes** : cartes avec catégorie, boutons « Voir la recette » (composant React `Recipes.jsx`) et « Imprimer » (PDF). La fiche s'ouvre en fenêtre centrée (ordinateur) ou **monte du bas de l'écran** (mobile) : photo, ingrédients dans un encadré, étapes avec retours à la ligne conservés, bouton « Imprimer la recette ». Correction au passage : le lien PDF avait une apostrophe parasite dans l'URL.
- **Coups de cœur** : cartes avec étiquette de catégorie, lieu, « Visiter → » (nouvel onglet) si une URL existe.
- **Filtres par catégorie** (`_category_filter.html.twig`, blog/recettes) : barre blanche flottante centrée, pastilles 44px, active en `$ink`. Envoi automatique au clic.
- **Pages vides** : message `.empty-state` au lieu d'une grille vide (poteries, évènements, filtres sans résultat).
- **Pied de page** : trois colonnes (présentation + réseaux, liens, adresse) qui s'empilent sur mobile ; « © année · Mentions légales · 🔒 » (cadenas = accès admin discret, gardé groupé avec « Mentions légales » pour ne jamais passer seul à la ligne).
- **Connexion / mot de passe oublié** : carte blanche centrée sur photo sombre (au lieu du dégradé vert).
- **Page 404** : photo `macro.jpg` plein écran, boutons blanc / contour. `error.html.twig` (500) reste **volontairement autonome** (pas de base, pas d'Encore).
- **Boutons** : `.btn-all` garde les coins asymétriques (`14px 0 14px 0`) avec 4 variantes (`btn-light-solid`, `btn-light-outline` sur photo ; `btn-dark-solid`, `btn-dark-outline` sur fond clair) ; `.btn-pill` pour les actions dans les cartes.

## Responsive

Vérifié le 07/10/2026 (Playwright, 1440 / 1024 / 390px, toutes les pages publiques + 404 + connexion) : aucun débordement horizontal ; menu, visionneuse, fiche recette et filtres testés en interaction, sans erreur JS. Cibles tactiles ≥ 44px.

## À garder en tête

- Courgette pour les titres, Open Sans pour le corps ; pas d'orange.
- Nouvelle page de section : `.page-hero` + modificateur avec sa photo, `.page-title`, `.page-lead`, puis `<main class="page-content" id="contenu">`.
- Couleurs et tailles : passer par `_variables.scss`, pas de valeurs en dur.
- Le favicon / icônes d'écran d'accueil (photo d'un plat aux ammonites sur émail rouge, 23/09/2026) et `site.webmanifest` n'ont pas changé.

## Historique avant la refonte (septembre 2026)

Titres réduits (19/09), galerie passée du masonry à une grille (19/09), menu mobile plein écran à la place du bouton « ●●● » et filtres en pastilles au lieu d'un `<select>` (23/09), fenêtre recette en portail React (23/09), pages d'erreur (23/09), nettoyage du pied de page (25/09).
