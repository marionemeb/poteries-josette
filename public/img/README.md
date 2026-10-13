# Photos du site

Chaque photo de fond existe en deux versions côte à côte : `nom.jpg` (pour
les vieux navigateurs) et `nom.webp` (plus légère, utilisée par les
navigateurs récents). Le CSS (`@include photo-background(...)`, dans
`assets/scss/_variables.scss`) et les photos dans les pages
(`templates/_picture.html.twig`) choisissent automatiquement.

**Pour remplacer une photo** : mettre le nouveau `nom.jpg`, puis refaire
`nom.webp` avec le même nom, par exemple :

    cwebp -q 80 -m 6 nom.jpg -o nom.webp

(ou n'importe quel outil d'export WebP, qualité 80). Si le `.webp` manque,
les navigateurs récents afficheront un fond vide : toujours mettre les deux.

`coquelicots.webp` est réduite à 1200 px de large (elle n'apparaît qu'en
petite tuile sur l'accueil). `poterie.jpg` n'a pas de version WebP : le
format n'y gagnait rien. Les images de `share/` (aperçus de partage) restent
en JPEG, le format le plus sûr pour les réseaux sociaux.
