# Design / identité visuelle — poteries-josette

Relevé le 18/09/2026 à partir du code (`assets/scss/*.scss`, `templates/base.html.twig`) — pas de maquette Figma ou charte graphique connue, ce document est reconstruit depuis l'existant.

## Typographie

- **Titres (h1–h4, gros titres de section)** : `Courgette` (police manuscrite/cursive, Google Fonts) — donne le ton artisanal/fait-main.
- **Corps de texte** : `Open Sans` (sans-serif, Google Fonts).
- Icônes : Font Awesome (chargé via kit CDN dans `base.html.twig`), pas de librairie d'icônes custom.

## Couleurs

Pas de palette de variables centralisée (couleurs en dur dans le SCSS) :
- **Fond général** : blanc (`#ffffff`)
- **Accent** : orange brûlé `#ff832d` (liens sur fond sombre), assombri au survol `rgba(199, 64, 18, 0.69)` / `#d7400a` (liens du footer)
- **Sombre / footer / mentions légales** : quasi-noir `#222424` / `#1d2124`
- **Ombre de texte sur les héros photo** : teinte bleu-pétrole foncé `#082b34`
- Texte blanc/`whitesmoke` sur toutes les sections à fond photo

## Structure visuelle

- **Chaque page a sa propre photo de fond en plein écran** (100vh sur l'accueil) avec un dégradé sombre superposé (`linear-gradient` noir → transparent) pour la lisibilité du texte blanc par-dessus : accueil (bouquet de coquelicots), atelier `/shop` (village d'Oingt), travaux `/work` (mains), blog (cœur), articles/galerie (poterie). Univers très photographique, chaleureux, ancré dans le terroir (Beaujolais/Oingt).
- **Navbar** : transparente, superposée à la photo de fond (`position: absolute`), liens blancs en `Open Sans` 24px, soulignement blanc au survol. En dessous de 992px : bouton hamburger (`fa-bars`, rond sombre semi-transparent en haut à droite) qui ouvre un **menu plein écran** (fond `rgba(29,33,36,0.97)`, liens centrés en `Courgette` 30px (« Accueil » ajouté en tête le 23/09/2026, aussi dans la barre desktop), croix pour fermer, touche Échap aussi, page derrière bloquée) — refait le 23/09/2026 à la place de l'ancien bouton « ●●● » + petit bloc sombre serré, jugé « très moche » par l'utilisateur. Toggle en JS maison (`assets/js/app.js`), plus le collapse Bootstrap. Entre 992 et 1199px, liens resserrés (20px) et `nowrap` pour tenir sur une ligne (ils passaient sur deux lignes sur tablette paysage).
- **Titres de section** ("hero") : réduits le 19/09/2026 suite à un retour utilisateur ("trop gros") — **56px desktop, 30px mobile** (avant : 96px desktop, 40-50px mobile). Police `Courgette`, blancs, avec une bordure blanche en haut et en bas (accueil) ou juste en bas (galerie articles). `.articles-title` (`assets/scss/articles.scss`) est la classe partagée par la quasi-totalité des pages — elle est chargée globalement via `base.html.twig` (le bundle CSS "articles" est inclus sur toutes les pages, pas seulement `/articles`), donc modifier cette classe change le titre de toutes les pages d'un coup. `.h1-home` (`assets/scss/app.scss`, page d'accueil uniquement) suit la même taille par cohérence.
- **Boutons** ("btn-all") : coins asymétriques arrondis (`border-radius: 12px 0 12px 0`), fond blanc semi-transparent, texte blanc, ombre portée légère.
- **Cartes** (blog, articles) : ombre portée qui s'accentue au survol (`box-shadow` plus prononcée). Galerie d'articles (`/articles`) passée le 19/09/2026 d'un "masonry" (`card-columns`, colonnes désalignées dès qu'un titre est plus long) à une **grille CSS** (`display: grid`, 3 colonnes desktop → 1 colonne mobile) : cartes alignées par rangée, photo au ratio 4:3 fixe (`object-fit: cover`) qui s'étire pour occuper la hauteur restante, texte plus petit pour laisser la place à la photo. Clic sur une photo → lightbox plein écran sur fond noir, image entière non recadrée.
- **Favicon** (ajouté le 19/09/2026) : carré rouge uni, reprenant la couleur des poteries — `public/favicon*.png`, `favicon.ico`, `apple-touch-icon.png`, déclarés dans `base.html.twig`.
- **Filtre par catégorie** (`/blog`, `/recipes`) : depuis le 23/09/2026, pastilles cliquables (« Toutes » + une par catégorie, fond blanc translucide, pastille active en `#222424`) au lieu d'un `<select>` natif jugé « très moche » une fois déplié. Partial partagé `templates/_category_filter.html.twig` ; champs en `expanded: true` (radios) dans `SearchType`/`SearchRecipeType`, envoi automatique au clic (`assets/js/app.js`).
- **Fenêtre recette** (`assets/js/components/Recipes.jsx`, clic sur l'œil) : refaite le 23/09/2026 — rendue via `ReactDOM.createPortal` dans `<body>` (dans la carte, `position: fixed` restait confiné à la carte à cause de la mise en page en colonnes), carte blanche centrée max 560px, défilement si longue, croix ronde, Échap/clic sur le fond pour fermer, image masquée si introuvable, `z-index` au-dessus du bouton hamburger.
- **Footer** : fond sombre, liens blancs virant orange au survol, icônes Font Awesome pour les infos de contact/localisation.

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
