SecuPlus-SSH
============

Ce programme vous prévient des connexion SSH sur votre poste.
Mais il affiche aussi la page de l'intra avec le profil de la personne attaquante.

Le screenshot ci-dessous est là pour vous présenter la manière de positionner le terminal.

![Petite vue](/small.png?raw=true "Petite vue")

## Positionnement

De préférence dans un coin de votre écran/terminal :
 - splittez (CMD + SHIFT + D) votre terminal pour avoir deux instances,
 - exécutez via la commande `php`, le script `secu.php` en haut ou en bas au choix,
 - dans la partie restante, faites de même pour `stdin.php` (étant le prompt).

## Comment ça marche

Pour commencer, redimensionnez `secu.php` pour qu'il atteigne 90 (cols) x 5 (rows).

Dés que la taille est correct, l'ID du serveur s'affiche en haut à gauche avec RAS en gros.
Deux solutions s'offrent à vous :
 - Soit le prompt vous demande une connexion immédiatement au lancement vers le serveur trouvé,
 - Soit il vous faut écrire l'ID (code numérique) représentant le serveur, puis valider.

Une fois la liaison éfectuée, vous pouvez lancer la commande désiré pour faire réagir le serveur.

## Notes

Commandes en fonction à ce jour :
 - enable  : active l'option choisie (liste ci-dessous),
 - disable : désactive l'option choisie (liste ci-dessous),
 - say     : fait parler OS X via la commande osascript de la machine,
 - beep    : lance le beep génant du système, tout simplement,
 - exit    : quitte le serveur, ferme le tunnel et quitte aussi le client.

Liste des options disponibles (activable/désactivable) :
 - intra   : ouvre le navigateur automatiquement à l'interception d'un intrusion,
 - count   : affiche le nombre d'intrusion total depuis le démarrage du serveur,
 - date    : affiche la date de la dernière intrustion en heures, minutes, et secondes.

/!\ La version (de démo) en cours ne présente aucune fonction intéressante.

![Grande vue](/big.png?raw=true "Grande vue")
