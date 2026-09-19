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
- **Navbar** : transparente, superposée à la photo de fond (`position: absolute`), liens blancs en `Open Sans` 24px, soulignement blanc au survol. En dessous de 992px, bascule en menu hamburger avec fond sombre `#222424`.
- **Titres de section** ("hero") : réduits le 19/09/2026 suite à un retour utilisateur ("trop gros") — **56px desktop, 30px mobile** (avant : 96px desktop, 40-50px mobile). Police `Courgette`, blancs, avec une bordure blanche en haut et en bas (accueil) ou juste en bas (galerie articles). `.articles-title` (`assets/scss/articles.scss`) est la classe partagée par la quasi-totalité des pages — elle est chargée globalement via `base.html.twig` (le bundle CSS "articles" est inclus sur toutes les pages, pas seulement `/articles`), donc modifier cette classe change le titre de toutes les pages d'un coup. `.h1-home` (`assets/scss/app.scss`, page d'accueil uniquement) suit la même taille par cohérence.
- **Boutons** ("btn-all") : coins asymétriques arrondis (`border-radius: 12px 0 12px 0`), fond blanc semi-transparent, texte blanc, ombre portée légère.
- **Cartes** (blog, articles) : ombre portée qui s'accentue au survol (`box-shadow` plus prononcée), disposition en colonnes façon "masonry" (`card-columns`, 3 colonnes desktop → 1 colonne mobile) pour la galerie d'articles.
- **Footer** : fond sombre, liens blancs virant orange au survol, icônes Font Awesome pour les infos de contact/localisation.

## Responsive

Basé sur les breakpoints Bootstrap 4 standards (575.98 / 767.98 / 991.98 / 1199.98px) :
- Titres de héros réduits (40–50px) sous 768px
- Navbar : hamburger + fond plein sous 992px
- Galerie articles : 3 colonnes → 1 colonne sous 576px

## À garder en tête en cas de modification UI

- Toujours utiliser `Courgette` pour les titres et `Open Sans` pour le corps — c'est ce qui donne le cachet "fait main" du site, ne pas basculer sur une police neutre/corporate.
- Respecter le principe "photo pleine page + dégradé sombre + titre blanc en Courgette" pour toute nouvelle page de section, plutôt que d'introduire un nouveau pattern de mise en page.
- Les couleurs ne sont pas centralisées en variables SCSS : si une refonte visuelle est envisagée, ce serait le moment d'introduire des variables de couleur/typo partagées plutôt que de continuer à dupliquer des valeurs en dur (piste déjà notée dans "Pistes d'amélioration" du fichier de suivi principal, catégorie Technique).
