# Design / identité visuelle — poteries-josette

Relevé le 18/09/2026 à partir du code (`assets/scss/*.scss`, `templates/base.html.twig`) — pas de maquette Figma ou charte graphique connue, ce document est reconstruit depuis l'existant.

## Typographie

- **Titres (h1–h4, gros titres de section)** : `Courgette` (police manuscrite/cursive, Google Fonts) — donne le ton artisanal/fait-main.
- **Corps de texte** : `Open Sans` (sans-serif, Google Fonts).
- Icônes : Font Awesome (chargé via kit CDN dans `base.html.twig`), pas de librairie d'icônes custom.

## Couleurs

Pas de palette de variables centralisée (couleurs en dur dans le SCSS) :
- **Fond général** : blanc (`#ffffff`)
- **Plus d'accent orange depuis le 23/09/2026** (l'utilisateur n'aime pas l'orange) : liens de contenu en blanc souligné, survols en blanc plus pâle (`rgba(255,255,255,0.65–0.75)`) ou gris foncé (`#3a3d3d` pour le bouton « remonter en haut », `#222424`/`#6c7070` pour les icônes des cartes recettes). Ne pas réintroduire d'orange.
- **Sombre / footer / mentions légales** : quasi-noir `#222424` / `#1d2124`
- **Ombre de texte sur les héros photo** : teinte bleu-pétrole foncé `#082b34`
- Texte blanc/`whitesmoke` sur toutes les sections à fond photo

## Structure visuelle

- **Chaque page a sa propre photo de fond en plein écran** (100vh sur l'accueil) avec un dégradé sombre superposé (`linear-gradient` noir → transparent) pour la lisibilité du texte blanc par-dessus : accueil (bouquet de coquelicots), atelier `/shop` (village d'Oingt), travaux `/work` (mains), blog (cœur), articles/galerie (poterie). Univers très photographique, chaleureux, ancré dans le terroir (Beaujolais/Oingt).
- **Navbar** : transparente, superposée à la photo de fond (`position: absolute`), liens blancs en `Open Sans` 24px, soulignement blanc au survol. En dessous de 992px : bouton hamburger (`fa-bars`, rond sombre semi-transparent en haut à droite) qui ouvre un **menu plein écran** (fond `rgba(29,33,36,0.97)`, liens centrés en `Courgette` 30px (« Accueil » ajouté en tête le 23/09/2026, aussi dans la barre desktop), croix pour fermer, touche Échap aussi, page derrière bloquée) — refait le 23/09/2026 à la place de l'ancien bouton « ●●● » + petit bloc sombre serré, jugé « très moche » par l'utilisateur. Toggle en JS maison (`assets/js/app.js`), plus le collapse Bootstrap. Avec 7 liens (« Accueil » compris), taille pleine (24px) seulement à partir de 1440px ; 22px entre 1200 et 1439px, 18px entre 992 et 1199px, `nowrap` partout — mesuré au pixel près le 23/09/2026 (ça débordait de l'écran entre 1200 et 1365px et à 992px).
- **Titres de section** ("hero") : réduits le 19/09/2026 suite à un retour utilisateur ("trop gros") — **56px desktop, 30px mobile** (avant : 96px desktop, 40-50px mobile). Police `Courgette`, blancs, avec une bordure blanche en haut et en bas (accueil) ou juste en bas (galerie articles). `.articles-title` (`assets/scss/articles.scss`) est la classe partagée par la quasi-totalité des pages — elle est chargée globalement via `base.html.twig` (le bundle CSS "articles" est inclus sur toutes les pages, pas seulement `/articles`), donc modifier cette classe change le titre de toutes les pages d'un coup. `.h1-home` (`assets/scss/app.scss`, page d'accueil uniquement) suit la même taille par cohérence.
- **Boutons** ("btn-all") : coins asymétriques arrondis (`border-radius: 12px 0 12px 0`), fond blanc semi-transparent, texte blanc, ombre portée légère.
- **Cartes** (blog, articles) : ombre portée qui s'accentue au survol (`box-shadow` plus prononcée). Galerie d'articles (`/articles`) passée le 19/09/2026 d'un "masonry" (`card-columns`, colonnes désalignées dès qu'un titre est plus long) à une **grille CSS** (`display: grid`, 3 colonnes desktop → 1 colonne mobile) : cartes alignées par rangée, photo au ratio 4:3 fixe (`object-fit: cover`) qui s'étire pour occuper la hauteur restante, texte plus petit pour laisser la place à la photo. Clic sur une photo → lightbox plein écran sur fond noir, image entière non recadrée.
- **Favicon** (ajouté le 19/09/2026, carré rouge uni à l'origine ; photo depuis le 23/09/2026, voir ci-dessous) — `public/favicon*.png`, `favicon.ico`, `apple-touch-icon.png`, déclarés dans `base.html.twig`. **Icônes d'écran d'accueil** (23/09/2026) : photo d'un plat de Josette (ammonites en terre sur émail rouge), recadrée serrée — `public/apple-touch-icon.png` (180), `icon-192x192.png`, `icon-512x512.png` (aussi `maskable`, lisible en rond comme en carré arrondi). Favicon d'onglet (`favicon*.png`, `favicon.ico` 16/32/48) remplacé le même jour par un cadrage encore plus serré de la même photo (une seule ammonite + le rouge), le plus lisible en 16–32px. Manifest `public/site.webmanifest` : nom « Les Poteries de Josette » / « Poteries Josette », `display: minimal-ui` (garde un bouton retour), couleurs `#222424`.
- **Filtre par catégorie** (`/blog`, `/recipes`) : depuis le 23/09/2026, pastilles cliquables (« Toutes » + une par catégorie, fond blanc translucide, pastille active en `#222424`) au lieu d'un `<select>` natif jugé « très moche » une fois déplié. Partial partagé `templates/_category_filter.html.twig` ; champs en `expanded: true` (radios) dans `SearchType`/`SearchRecipeType`, envoi automatique au clic (`assets/js/app.js`).
- **Fenêtre recette** (`assets/js/components/Recipes.jsx`, clic sur l'œil) : refaite le 23/09/2026 — rendue via `ReactDOM.createPortal` dans `<body>` (dans la carte, `position: fixed` restait confiné à la carte à cause de la mise en page en colonnes), carte blanche centrée max 560px, défilement si longue, croix ronde, Échap/clic sur le fond pour fermer, image masquée si introuvable, `z-index` au-dessus du bouton hamburger.
- **Pages d'erreur** (23/09/2026, `templates/bundles/TwigBundle/Exception/`) : fond `/img/macro.jpg` (gros plan coquelicot céramique) + dégradé sombre + titre `Courgette`. `error404.html.twig` étend `base.html.twig` (menu/footer, boutons « Retour à l'accueil » / « Voir les poteries »). `error.html.twig` (500 et tout le reste) est **volontairement autonome** — pas de `base.html.twig`, pas de BDD, pas d'Encore, CSS en ligne — pour s'afficher même quand c'est justement l'un de ces éléments qui plante ; donne le téléphone/e-mail de Josette. Prévisualisables en dev via `/_error/404`, `/_error/500`. Nouveau dossier `templates/bundles/` : nécessite un `cache:clear` pour être pris en compte (fait automatiquement au déploiement).
- **Footer** (`templates/_footer.html.twig`, styles dans `assets/scss/app.scss`) : fond `#222424`, liens blancs (blanc à 65 % au survol), trois colonnes (présentation + réseaux sociaux, liens, adresse/contact) qui s'empilent sur mobile. Nettoyé le 25/09/2026 : « © {année courante} Les Poteries de Josette · Mentions légales · 🔒 » en bas (sur deux lignes sous 576px, sans couper les liens), le cadenas gris estompé est l'accès admin de Josette (choix de l'utilisatrice : discret plutôt que supprimé), téléphone en lien `tel:`, libellés `aria-label` sur les icônes Facebook/Instagram, plus de tirets devant les liens, adresse vers Google Maps en lien direct (plus `goo.gl`). L'adresse affiche toujours « OINGT » (commune fusionnée en Val d'Oingt en 2017 — choix de l'utilisatrice de garder Oingt).

## Responsive

Basé sur les breakpoints Bootstrap 4 standards (575.98 / 767.98 / 991.98 / 1199.98px) :
- Titres de héros réduits (40–50px) sous 768px
- Navbar : hamburger + menu plein écran sous 992px
- Vérification complète le 23/09/2026 (Playwright, 375/768/1024px, toutes les pages publiques) : plus aucun débordement horizontal. Corrigés au passage : image de « Argile et émaux » (largeur fixe 400px, coupée sur mobile → pleine largeur sous 576px), e-mail coupé dans les mentions légales (`overflow-wrap`), titre « Adresse » du footer collé à la liste sur mobile, cartes d'événements à hauteur fixe (titre tronqué, dates coupées → hauteur auto).
- Galerie articles : 3 colonnes → 1 colonne sous 576px

## À garder en tête en cas de modification UI

- Toujours utiliser `Courgette` pour les titres et `Open Sans` pour le corps — c'est ce qui donne le cachet "fait main" du site, ne pas basculer sur une police neutre/corporate.
- Respecter le principe "photo pleine page + dégradé sombre + titre blanc en Courgette" pour toute nouvelle page de section, plutôt que d'introduire un nouveau pattern de mise en page.
- Les couleurs ne sont pas centralisées en variables SCSS : si une refonte visuelle est envisagée, ce serait le moment d'introduire des variables de couleur/typo partagées plutôt que de continuer à dupliquer des valeurs en dur (piste déjà notée dans "Pistes d'amélioration" du fichier de suivi principal, catégorie Technique).
