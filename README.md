Symfony User Management Project

Ce projet implémente un système de gestion d'utilisateurs avec des fonctionnalités telles que l'inscription, la connexion, la mise à jour des informations, et la gestion des sessions. Il est construit avec Symfony et utilise une base de données MySQL pour stocker les informations des utilisateurs et des sessions.

Fonctionnalités
Inscription : Permet aux utilisateurs de créer un compte avec un nom d'utilisateur, un e-mail, un mot de passe, et un rôle.
Connexion : Les utilisateurs peuvent se connecter avec leur nom d'utilisateur et leur mot de passe. Un token d'authentification est généré pour chaque session.
Déconnexion : Le token d'authentification est supprimé et l'utilisateur est redirigé vers la page de connexion.
Mise à jour du profil : Permet aux utilisateurs de mettre à jour leur nom d'utilisateur, e-mail, mot de passe et rôle.
Suppression de compte : Permet aux utilisateurs de supprimer leur compte ainsi que la session associée.
Importation CSV : Permet d'importer un fichier CSV contenant plusieurs utilisateurs et de les enregistrer en base de données.
Protection des routes : Accès sécurisé aux pages en vérifiant la validité du token d'authentification dans les cookies.


Prérequis
PHP 8.1 ou supérieur
Symfony 6.0 ou supérieur
Doctrine ORM
MySQL ou une autre base de données compatible
Composer pour gérer les dépendances

Installation
Clonez ce dépôt sur votre machine locale :
git clone <url_du_dépôt>
cd <dossier_du_projet>

Installez les dépendances via Composer :
composer install

Configurez votre base de données dans .env :
DATABASE_URL="mysql://username:password@localhost:3306/db_name"

Créez et migrez la base de données :
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

Démarrez le serveur Symfony :
symfony serve


Accédez à l'application à l'adresse http://localhost:8000.


Structure du projet
src/Controller/: Contient les contrôleurs pour gérer les actions liées aux utilisateurs (UserController, HomeController).
src/Entity/: Contient les entités User, UserSession et Infos pour représenter les données utilisateurs.
templates/: Contient les vues Twig pour afficher les pages web.
config/: Contient la configuration de Symfony et des services.

Sécurisation
Les tokens d'authentification sont stockés dans les cookies avec les attributs HttpOnly et Secure pour éviter les attaques par script intersite (XSS). Les sessions ont une durée de vie limitée et sont invalidées après leur expiration.
