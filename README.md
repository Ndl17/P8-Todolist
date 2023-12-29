# Projet 8 OpenClassrooms - Améliorez une application existante de ToDo & Co

## Score de qualité du code :
[![SymfonyInsight](https://insight.symfony.com/projects/5ce40f7e-3431-421e-94fa-3366b8b3e088/big.svg)](https://insight.symfony.com/projects/5ce40f7e-3431-421e-94fa-3366b8b3e088)
## Informations :

## Identifiants pour se connecter :

#### Utilisateur Admin :
* Identifiant : admin@todolist.com
* Mot de Passe : password

#### Utilisateur User :
* Identifiant : user@user.fr
* Mot de Passe : password

## Prérequis :
* PHP 8.3, Composer, Symfony 6.4. 

## Installation :
* Etape 1 : Installez l’ensemble des fichier de ce repo dans le dossier web de votre environnement local.
* Etape 2 : Modifiez les constantes du fichier .env  selon les information de votre bdd: 
DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8.0.32&charset=utf8mb4"
* Etape 3 :  Effectuez la commande "composer install" depuis le répertoire du projet cloné
* Etape 4 : Effectuez la commande php bin/console doctrine:database:create pour créer la base de données 
* Etape 5 : Pour recréer la structure de la bdd, lancez la commande suivante : php bin/console doctrine:migrations:migrate
* Etape 6 : Pour recréer le jeu de donnée: php bin/console doctrine:fixtures:load

* Etape 7 : Démarrez le projet en utilisant la commande suivante : php bin/console server:start, accédez à l'application via l'url suivante: http://127.0.0.1:8000/
## Tests :
Pour lancer les tests :
* Etape 1 : Ouvrir un terminal à la racine du projet 
* Etape 2 : Lancer la commande « vendor/bin/phpunit », pour obtenir un rapport de couverture de code lancer : « php bin/phpunit --coverage-html docs/code-coverage »
En cas de test, bien penser :
* Configurer la bdd de test dans .env.test en y incluant les informations de votre bdd :  DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8&charset=utf8mb4"
* Charger les fixtures : php bin/console doctrine:fixtures:load --env=test 

## Librairies utilisées :
* FakerPhp

