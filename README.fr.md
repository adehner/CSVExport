CSV Export (plugin pour Omeka)
==============================

Permet aux utilisateurs [Omeka] d’exporter des métadonnées des contenus vers un fichier CSV (valeurs séparées par des virgules), en mappant les données sur les entêtes de colonne CSV. Chaque ligne du fichier représente les métadonnées d’un seul document. Ce plugin est utile pour exporter des données d’un site Omeka et les éditer dans une feuille de calcul ou dans OpenRefine. En utilisant le plugin [CSV Import +] de Daniel Berthereau, les métadonnées peuvent être réimportées dans Omeka, en écrasant ou complétant les métadonnées des documents existant dans Omeka. Lorsqu’une notice Omeka est modifiée avec un traitement en lot, l’URL de référence et l’ID des notices restent inchangés.


Instructions
------------

Reportez-vous au [screencast] et à la [documentation écrite] d’Omeka intitulée « Installation des thèmes et des thèmes » pour obtenir des instructions détaillées sur l’installation des plugins Omeka.

Le plug-in d’export CSV permet d’exporter un lot de notices Omeka sous forme d’un fichier .csv.

### Configuration du plugin

Par défaut, le plugin exporte toutes les métadonnées Dublin Core contenues dans ces notices. Cependant, d’autres jeux de métadonnées à exporter peuvent être séléctionnés partir de la page de configuration du plugin. Les métadonnées qui n’appartiennent pas à des jeux (identifiant, mots-clés, etc.) peuvent également être exportés si besoin.

![Alt text](/csvexport_options.png?raw=true)

La page de configuration permet notamment de configurer les colonnes du tableur.
- entêtes des colonnes : il est possible de choisir un nom complet "Dublin Core : Title" ou le nom simple "Title".
- activation des filtres Omeka : Omeka permet de modifier l’affichage de certains éléments par des filtres, par exemple pour créer des liens sur certaines données. L’option évite de les appliquer et permet donc de conserver la donnée telle qu’elle est enregistrée.
- éléments non utilisés : le plus souvent, de nombreux éléments ne sont pas utilisées dans les notices, mais il peut être utile d’avoir des colonnes vides, par exemple en cas de modification et réimport.
- valeurs multiples : certains éléments ont souvent plusieurs valeurs, par exemple un article avec plusieurs auteurs. L’option permet de retourner d’avoir plusieurs colonnes avec le même nom mais une valeur par cellule, ou à l’inverse une seule colonne par élément et plusieurs valeurs dans la cellule, séparées par le séparateur multivaleur.
- saut de ligne : certains tableurs ne permettent pas les sauts de ligne à l'intérieur des cellules. Une option permet de les convertir en « retour chariot ».

### Pour exporter tous les documents de votre base Omeka, suivez ces étapes :

![Alt text](/csvexport_all.png?raw=true)

1. Connectez-vous à votre tableau de bord Omeka.
2. Cliquez sur « Export CSV » dans la barre latérale gauche pour ouvrir la page « Export CSV ».
3. Cliquez sur le bouton « Exporter toutes les données au format CSV » pour télécharger un fichier .csv des notices vers votre bureau.
4. Faites une copie du fichier .csv pour sauvegarde au cas où. Nommez la copie comme vous le souhaitez.

### Pour exporter un sous-ensemble de vos documents Omeka, procédez ainsi :

![Alt text](/csvexport_browse.png?raw=true)

1. Connectez-vous à votre tableau de bord Omeka.
2. Utilisez le formulaire de Recherche avancée du tableau de bord pour définir un lot de documents (par exemple, les documents d’une seule collection).
3. Le lot de documents est affiché sous forme d’une liste dans une page « Parcourir les contenus », comme dans la capture d’écran ci-dessous. Lorsque vous avez le lot souhaité, cliquez sur le bouton « Exporter les résultats au format CSV » en bas de page et sauvegardez le fichier .csv.
4. Faites une copie du fichier .csv pour sauvegarde au cas où. Nommez la copie comme vous le souhaitez.

Problèmes connus et améliorations futures
-----------------------------------------

- Actuellement, un lot ne peut être exporté que s’il est créé via le formulaire de recherche avancée. La navigation en cliquant sur « Mots-clés » ou « Collections » dans la barre latérale gauche, et le filtrage (avec un filtre rapide) ne fonctionneront pas comme prévu.
- Actuellement, les champs exportés sont définis dans la configuration du plugin, pas lors de l’export.
- Le plugin n’utilise pas les tâches d’arrière-plan. Pour les grandes bases de données, augmentez la durée maximale du serveur et la taille de la mémoire.

Avertissement
-------------

À utiliser à vos risques et périls.

Il est toujours recommandé de sauvegarder ses fichiers et ses bases de données et de vérifier ses archives régulièrement afin de pouvoir les restaurer en cas de besoin.

Dépannage
---------

Voir les problèmes en ligne sur la page [issues] du plugin sur GitHub.

Copyright
---------

* Copyright Anneliese Dehner, 2017
* Copyright Daniel Berthereau, 2019 (cf. [Daniel-KM] sur GitHub)

[Omeka]: https://omeka.org/classic
[CSV Import +]: https://github.com/Daniel-KM/CsvImportPlus
[screencast]: https://vimeo.com/153819886
[documentation écrite]: http://omeka.org/codex/Managing_Plugins_2.0
[issues]:  https://github.com/adehner/CSVExport/issues
[Daniel-KM]: https://github.com/Daniel-KM "Daniel Berthereau"
