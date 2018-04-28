# L2BCMS
###### Learn to Build Content management system (CMS).
###### Apprendre à construire un Système de gestion de contenu

## [*Architecture*] Modèle MVC

La construction d'un CMS s'effectue d'abord par la mise en place du modèle de conception M-V-C (modèle vue contrôle).

CUSTOM CMS

├──[content] Les modèles de contenu 

│ . . . . ├── Les traductions de langues

│ . . . . ├── Les modules

│ . . . . └── Les plugins

├──[includes] La partie contrôle

│ . . . . ├── La bibliothèque PHP

│ . . . . ├── (SPL) Moteur d'auto chargement de dépendance (pour charger la bibliothèque)

│ . . . . ├── (Hook) Système de chargement automatique des plugins et modules

│ . . . . └── Les configurations et permissions

├──[template] Les modèles d'affichage des pages

└── Les pages de navigation du CMS (partie vue du modèle MVC)

## [*compatibilité et la sécurité*] Le PHP

Le codage en PHP doit prendre en compte les points suivants:

* L'environnement du Serveur Web
* L'utilisation d'une ou plusieurs Bases de données
```
Attention à l'installation du bon Driver de connexion sur l'environnement Machine
```
* La Version du PHP
* La protection de toutes les entrées et sorties de l'interprétateur PHP
```

- Protection du CGI (injection HTTP) ou FPM (sécurité sur les sockets).
- Sanitizer permanent sur les verbes HTTP (GET - POST - HEAD - OPTIONS - CONNECT - TRACE - PUT - PATCH - DELETE ).
- Complexifier l'utilisation des cookies par des vérifications complexes sur l'identification du client et du serveur.
- Toujours Utiliser un Cryptage de dernière génération.
- Installez des LOGS pour garder la trace des attaques potencielles.
- Désactivez l'utilisation de méthode interface système (mail, command, ressource, socket, ...)
- L'obfuscation peut être rajoutée au dessus de la sécurité existante.

```