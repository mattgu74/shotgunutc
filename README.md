ShotgunUTC
==========

ShotgunUTC est une micro-billetterie, pour les associations de l'UTC.

Dépendances ecosystème UTC
--------------------------

Cette application est dépendante de [payutc](https://github.com/payutc/server) pour réaliser la partie paiement en ligne, et gestion des droits pour la partie administrative.

Elle est égallement dépendante de [ginger](https://github.com/simde-utc/ginger) pour vérifier si les utilisateurs sont bien cotisants au [BDE-UTC](https://assos.utc.fr/), et récupérer leurs infos personnelles (nom, prénom, email)

L'authentification des utilisateurs, se fait elle en utilisant le système d'authentification centralisé [CAS](http://www.jasig.org/cas).

Installation
------------

```
curl -s https://getcomposer.org/installer | php
./composer.phar install
```

Puis accèder à `http://my.url/shotgunutc/install`.


Auteur/Licence
--------------

Cette application a été produite par [Matthieu Guffroy](http://www.mattgu.com).
Et est soumis à la licence [Beerware](http://fr.wikipedia.org/wiki/Beerware).


