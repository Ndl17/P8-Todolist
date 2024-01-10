# Guide de contribution au projet

Ce document sert de guide pour tous les développeurs travaillant sur le projet. Il détaille comment collaborer efficacement et suivre les processus de qualité. Chaque développeur doit travailler dans un environnement local qui reflète le plus fidèlement possible l'environnement de production.

## Comment contribuer au projet :

* Etape 1 : Réaliser un fork du répertoire Github du projet
* Etape 2 : Cloner localement de votre fork : git clone https://github.com/Pseudo/nomdemonrepo.git
* Etape 3 :	Installer le projet et ses dépendances (Consulter le readme du projet)
* Etape 4 : Créer une branche : git checkout -b nouvelle-branch
* Etape 5 : Penser à commit chacune de vos modifications
* Etape 6 : Executez tous vos tests
* Etape 7 : Push la branch sur votre fork
* Etape 8 : Ouvrir une pull request sur le répertoire Github du projet

## Processus de Qualité

### Tests :

* Etape 1 : Écrivez des tests pour chaque nouvelle fonctionnalité.
* Etape 2 :	Exécutez l'ensemble des tests avant de soumettre une PR.
* Etape 3 :	Lancer les tests avec génération d'un rapport de code coverage : php bin/phpunit --coverage-html docs/code-coverage

### En cas de test, bien penser à :

* Configurer la bdd de test dans .env.test en y incluant les informations de votre bdd :  DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8&charset=utf8mb4"

* Charger les fixtures : php bin/console doctrine:fixtures:load --env=test 
(Pour implémenter de nouveaux tests, se référer à la documentation officielle de Symfony)

NB : à noter également, afin de contrôler la qualité du code une analyse symfony insight doit être réalisée à chaque PR. De façon à détecter d’éventuelles anomalies non perçues durant le développement.

## Règles à Respecter:

* Documentation : Tout code complexe doit être accompagné de commentaires explicatifs (PHP Doc pour les nouvelles fonctions).

* Commit Messages : Écrivez des messages de commit clairs et descriptifs.

* Sécurité : Soyez vigilant sur la sécurité du code et suivez les meilleures pratiques de Symfony. (Ne jamais faire confiance à l’utilisateur).

* Standards : Respectez les standards PSR pour PHP.

* DRY : Du mieux que vous pouvez éviter les redondance de code 


