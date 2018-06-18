# ignDownloader

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/45f1e17deef6422187a662dee75b5920)](https://www.codacy.com/app/amalricBzh/ignDownloader?utm_source=github.com&utm_medium=referral&utm_content=amalricBzh/ignDownloader&utm_campaign=badger)

Télécharge une portion de photo aérienne IGN. Assemble tout en une seule image.

# Comment ça marche ?

1. git clone https://github.com/amalricBzh/ignDownloader
2. composer update

Ca devrait suffire si vous mettez ça sur un serveur web. Si une erreur s'affiche vous indiquant qu'il y a peut-être une
erreur de proxy : éditez le fichier /src/config.php, mettez la valeur `useProxy` à `true` et indiquez l'URL du
proxy (laissez le protocole tcp) et vos login/mot de passe.

# Quelles évolutions sont prévues ?

Au lieu de rentrer les coordonnées par numéros de ligne et de colonne, entrer les coordonnées géographiques (longitude et lattitude).

# Historique

## v0.2
Gestion possible d'un proxy.
## v0.1
Première version fonctionnelle basée sur le framework Slim.
