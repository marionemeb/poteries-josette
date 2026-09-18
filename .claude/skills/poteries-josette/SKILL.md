---
name: poteries-josette
description: Skill dédié au projet "poteries-josette" — le site vitrine et boutique de la potière artisanale https://www.poterie-josette.com/ (dépôt Symfony 4.4 / PHP / Doctrine / EasyAdmin / Webpack Encore). Utiliser dès que l'utilisateur mentionne "poteries-josette", "poterie josette", le site de la potière, ou veut travailler sur ce dépôt, faire un point, reprendre le fil, ou avancer sur ce projet. Contient dans assets/projet-poteries-josette.md un instantané complet du contexte technique (stack, entités, contrôleurs, comptes Git/GitHub/Claude Code configurés pour ce repo) — à charger en premier pour retrouver tout le contexte avant de répondre ou d'aider sur ce projet.
---

# Projet poteries-josette

Ce skill donne à Claude tout le contexte du projet **poteries-josette** : le site vitrine + boutique d'une potière artisanale (https://www.poterie-josette.com/), un dépôt Symfony 4.4.

Ce skill est scopé à ce dépôt (`.claude/skills/poteries-josette/`, versionné avec le repo) — il n'est disponible que dans les sessions Claude Code ouvertes ici.

## Première étape obligatoire

Avant de répondre à toute question sur ce projet, lire `assets/projet-poteries-josette.md`. Ce fichier contient : résumé, stack technique, architecture (entités/contrôleurs clés), configuration des comptes Git/GitHub/Claude Code dédiés à ce repo, historique récent, état des lieux/TODO et pistes d'amélioration. Le design/identité visuelle du site (typographie, couleurs, structure des pages) est dans un fichier séparé, `assets/design-poteries-josette.md` — le lire dès que la demande touche à l'apparence du site, l'UI/UX, ou avant toute modification de template/CSS.

**Important** : ce fichier est un instantané qui ne se met à jour que quand quelqu'un l'édite explicitement (voir "Comment se comporter" ci-dessous). Pour l'état *actuel* du code (dernier commit, fichiers modifiés, structure exacte), se fier à une lecture directe du dépôt (`git log`, exploration des fichiers) plutôt qu'à ce snapshot, qui documente le contexte produit/compte, pas le code au jour le jour.

## Comment se comporter

- **L'utilisateur donne une info nouvelle sur le projet** (décision produit, nouvelle fonctionnalité livrée, changement de config Git/GitHub/Claude Code, incident résolu...) → mettre à jour la bonne section de `assets/projet-poteries-josette.md` directement (fichier normal du repo, pas en lecture seule), mettre à jour la ligne "Dernière mise à jour le {DATE}".
- **L'utilisateur veut un état des lieux ou reprendre le fil** ("où en est poteries-josette ?", "on avait fait quoi la dernière fois ?") → résumer à partir du fichier, sans réinventer ce qui n'y figure pas.
- **L'utilisateur veut coder/déboguer sur le repo** → ce skill donne le contexte produit et compte ; pour le détail du code, explorer directement les fichiers du dépôt plutôt que de se fier uniquement au snapshot.
- Ne jamais inventer une tâche, une décision ou un état de compte qui ne figure ni dans ce skill ni dans une vérification directe du dépôt.

## Fin de session

Si des informations nouvelles ont été ajoutées pendant la conversation, éditer directement `assets/projet-poteries-josette.md` (fichier versionné du repo, comme n'importe quel autre fichier) — pas besoin de régénérer le skill ni de passer par un export externe. Si l'utilisateur veut que ce suivi soit visible par d'autres personnes ayant accès au dépôt, lui rappeler de commiter ces changements comme le reste du code.
