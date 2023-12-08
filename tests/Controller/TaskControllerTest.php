<?php

namespace App\Tests\Controller;

use App\Tests\Trait\TestClientUtilitiesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{
    // on importe le trait qui contient les fonctions de connexion et de récupération de tâche
    use TestClientUtilitiesTrait;

/***************** TEST FONCTION INDEX() *******************/
    public function testIndexTaskDisplayPage(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        // on demande au client de requêter une URL
        $client->request('GET', '/tasks');
        //on test que la requete renvoie un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

    }

    public function testIndexHasTask(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $crawler = $client->request('GET', '/tasks');
        //on compte le nombre de tâches affichées
        $taskCount = $crawler->filter('.card-task')->count();
        //On test que la page affiche au moins 15 tâches
        $this->assertGreaterThanOrEqual(15, $taskCount, "La page doit afficher au moins 15 tâches");
    }

/***************** TEST FONCTION CREATE() *******************/

    public function testCreateTaskDisplayPage(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $client->request('GET', '/tasks/create');
        //on test que la requete renvoie un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        //on test que le formulaire contient bien le bouton ajouter
        $this->assertSelectorTextContains('button', 'Ajouter');
    }

    public function testCreateValidTask(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $crawler = $client->request('GET', '/tasks/create');
        //on remplit le formulaire
        $form = $crawler->selectButton('Ajouter')->form([
            'task_form[title]' => 'Test Titre',
            'task_form[content]' => 'Test Contenu',
        ]);
        //on soumet le formulaire
        $client->submit($form);
        //on test que la requete renvoie un code 302
        $this->assertResponseRedirects('/tasks');
        //on suit la redirection
        $client->followRedirect();
        //on test que la requete renvoie un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        //on test que le message de succès est bien affiché
        $this->assertSelectorTextContains('.alert.alert-success', 'La tâche a été bien été ajoutée.');
    }

    public function testCreateInvalidTask(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        //on affiche la page qui permet de créer une tâche
        $crawler = $client->request('GET', '/tasks/create');
        //on remplit le formulaire

        $form = $crawler->selectButton('Ajouter')->form([
            'task_form[title]' => '',
            'task_form[content]' => '',
        ]);

        //on soumet le formulaire
        $client->submit($form);

        //on test que le message d'erreur est bien affiché
        $this->assertSelectorTextContains('.alert.alert-danger', 'La tâche n\'a pas été ajoutée. Veuillez recommencer votre saisie.');
    }

/***************** TEST FONCTION EDIT() *******************/
    public function testEditTaskDisplayPage(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        //on se connecte en tant que user
        $email = 'user@user.fr';
        //on récupère le client connecté
        $this->createAuthenticatedClient($client, $email);
        //on récupère la tâche de l'utilisateur connecté
        $getTask = $this->getTaskByUserId($client, $email);
        //on affiche la page qui liste les tâches
        $client->request('GET', '/tasks');
        //on recherche l'esemble des éléments h5
        $h5Titles = $client->getCrawler()->filter('h5.card-title');
        //on récupère le titre de la tâche créé par le user
        $expectedTitle = $getTask->getTitle();
        $titleFound = false;
        //on parcourt les éléments h5
        foreach ($h5Titles as $h5Title) {
            //on test si le titre de la tâche est bien présent dans les éléments h5
            if ($h5Title->textContent === $expectedTitle) {
                $titleFound = true;
                break;
            }
        }
        // si le titre n'est pas trouvé on affiche un message d'erreur
        $this->assertTrue($titleFound, "Le titre attendu n'a pas été trouvé dans les éléments h5.");
        //maintenant qu'on est connecté et que le titre existe on peut accéder à l'édition de la tâche
        $client->request('GET', '/tasks/' . $getTask->getId() . '/edit');
        //on test que la requete renvoie un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        //on test que le formulaire contient bien le bouton modifier
        $this->assertSelectorTextContains('button', 'Ajouter');

    }

    public function testEditTaskFailDisplayNotConnected(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $title = 'Tâche de test';
        //on recupère la tache en fonction du titre
        $getTask = $this->getTaskByTitle($client, $title);
        //on va sur la page d'édition de la tâche
        $client->request('GET', '/tasks/' . $getTask->getId() . '/edit');

        //on test la redirection vers la page des tâches
        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();
        //on test que le message d'erreur est bien affiché
        $this->assertSelectorTextContains('.alert.alert-danger', 'Oops ! Vous devez être connecté pour modifier une tâche.');
    }

    public function testEditTaskFailDisplayNotOwner(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email = 'user@user.fr';
        //titre de tache dont le user n'est pas l'auteur
        $title = 'Tâche de test bis';
        //on se connecte en tant que user
        $this->createAuthenticatedClient($client, $email);
        //on recupère la tache en fonction du titre
        $getTask = $this->getTaskByTitle($client, $title);
        //on va sur la page d'édition de la tâche avec une tache dont le user n'est pas l'auteur
        $client->request('GET', '/tasks/' . $getTask->getId() . '/edit');
        //on test la redirection vers la page des tâches
        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();
        //on test que le message d'erreur est bien affiché
        $this->assertSelectorTextContains('.alert.alert-danger', 'Oops ! Vous n\'êtes pas l\'auteur de cette tâche. Vous ne pouvez pas la modifier');
    }

    public function testEditTaskValid(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        //on se connecte en tant que user
        $email = 'user@user.fr';
        //on récupère le client connecté
        $this->createAuthenticatedClient($client, $email);
        //on récupère la tâche de l'utilisateur connecté
        $getTask = $this->getTaskByUserId($client, $email);
        //on affiche la page qui permet d'editer la tache dont le user est l'auteur
        $crawler = $client->request('GET', '/tasks/' . $getTask->getId() . '/edit');
        //  dd($crawler);
        $form = $crawler->selectButton('Ajouter')->form([
            'task_form[title]' => 'Tâche de test',
            'task_form[content]' => 'Contenu de la tâche de test',
        ]);
        //on soumet le formulaire
        $client->submit($form);
        //on test que la requete renvoie un code 302
        $this->assertResponseRedirects('/tasks');
        //on suit la redirection
        $client->followRedirect();
        //on test que la requete renvoie un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        //on test que le message de succès est bien affiché
        $this->assertSelectorTextContains('.alert.alert-success', 'Superbe ! La tâche a bien été modifiée.');
    }

    public function testEditTaskInvalid(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        //on se connecte en tant que user
        $email = 'user@user.fr';
        //on récupère le client connecté
        $this->createAuthenticatedClient($client, $email);
        //on récupère la tâche de l'utilisateur connecté
        $getTask = $this->getTaskByUserId($client, $email);

        //on affiche la page qui permet d'editer la tache dont le user est l'auteur
        $crawler = $client->request('GET', '/tasks/' . $getTask->getId() . '/edit');
        //on saisit les champs du formulaire avec des valeurs invalides
        $form = $crawler->selectButton('Ajouter')->form([
            'task_form[title]' => '',
            'task_form[content]' => '',
        ]);

        //on soumet le formulaire
        $client->submit($form);
        //on test que le message d'erreur est bien affiché
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

/***************** TEST FONCTION TOGGLE() *******************/

    public function testToggleTaskFailDisplayNotConnected(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $title = 'Tâche de test bis';
        //on récupère la tâche par son titre
        $getTask = $this->getTaskByTitle($client, $title);
        //on va sur la page qui permet de basculer le statut de la tâche sans être connecté
        $client->request('GET', '/tasks/' . $getTask->getId() . '/toggle');
        //on test la redirection vers la page des tâches
        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();
        //on test que le message d'erreur est bien affiché
        $this->assertSelectorTextContains('.alert.alert-danger', 'Oops ! Vous devez être connecté pour modifier une tâche.');
    }

    public function testToggleTaskFailDisplayNotOwner(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        //on se connecte en tant que user
        $email = 'user@user.fr';
        $title = 'Tâche de test bis';

        //on récupère le client connecté
        $this->createAuthenticatedClient($client, $email);
        //on récupère la tâche de l'utilisateur connecté
        $getTask = $this->getTaskByTitle($client, $title);
        //on va sur la page qui permet de basculer le statut de la tâche en étant connecté mais sans être l'auteur de la tâche
        $client->request('GET', '/tasks/' . $getTask->getId() . '/toggle');
        //on test la redirection vers la page des tâches
        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();
        //on test que le message d'erreur est bien affiché
        $this->assertSelectorTextContains('.alert.alert-danger', 'Oops ! Vous n\'êtes pas l\'auteur de cette tâche. Vous ne pouvez pas la modifier');
    }

    public function testToggleTaskValid(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email = 'user@user.fr';
        //on récupère le client connecté
        $this->createAuthenticatedClient($client, $email);
        //on récupère la tâche de l'utilisateur connecté
        $getTask = $this->getTaskByUserId($client, $email);

        //on récupère le statut de la tache avant le changement (clique sur le lien)
        $initialIsDone = $getTask->isIsDone();

        //on clique sur le lien qui permet de basculer le statut de la tâche (true/false)
        $crawler = $client->request('GET', '/tasks/' . $getTask->getId() . '/toggle');

        //on vérifie que la redirection est bien faite
        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();
        //on récupère le statut de la tache après le changement (true/false)
        $afterIsDone = $getTask->isIsDone();

        //on verifie que le statut de la tache a bien changé
        $this->assertNotEquals($initialIsDone, $afterIsDone, 'Le statut de la tâche soit changer.');

        //on vérifie que le message de succès est bien affiché  
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        //en fonction du statut de la tâche on vérifie que le message de succès  correspondant est bien affiché
        if ($afterIsDone == false) {
            $this->assertSelectorTextContains('.alert.alert-success', 'Superbe ! La tâche a bien été marquée comme non faite.');
        } else {
            $this->assertSelectorTextContains('.alert.alert-success', 'Superbe ! La tâche a bien été marquée comme faite.');
        }
    }

    public function testToggleTaskValidValue(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();

        // On récupère un utilisateur
        $email = 'user@user.fr';
        // On se connecte en tant que cet utilisateur
        $this->createAuthenticatedClient($client, $email);
        // On récupère une tâche de cet utilisateur
        $getTask = $this->getTaskByUserId($client, $email);

        //on clique sur le lien qui permet de basculer le statut de la tâche (true/false)
        $crawler = $client->request('GET', '/tasks/' . $getTask->getId() . '/toggle');

        //on vérifie que la redirection est bien faite
        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();
        //on récupère le statut de la tache après le changement (true/false)
        $afterIsDone = $getTask->isIsDone();
        //on verifie que le statut de la tache a bien changé et qu'il est bien égal à true ou false en fonction du statut initial
        if ($afterIsDone == false) {
            $this->assertEquals($afterIsDone, false, 'Le statut de la tâche doit être false.');
        } else {
            $this->assertEquals($afterIsDone, true, 'Le statut de la tâche doit être true.');
        }
    }

    public function testToggleTaskInvalidNonExistentTask(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        //on se connecte en tant que user
        $email = 'user@user.fr';
        //on récupère le client connecté
        $this->createAuthenticatedClient($client, $email);

        // Tenter de basculer une tâche avec un ID inexistant
        $client->request('GET', '/tasks/999999/toggle');

        // Vérifier que la réponse indique que la tâche n'existe pas
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

/***************** TEST FONCTION DELETE() *******************/

    public function testDeleteTaskFailDisplayNotConnected(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $title = 'Tâche de test bis';
        //on récupère la tâche par son titre
        $getTask = $this->getTaskByTitle($client, $title);
        //on va sur la page qui permet de supprimer la tâche sans être connecté
        $client->request('GET', '/tasks/' . $getTask->getId() . '/delete');

        //on test la redirection vers la page des tâches
        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();
        //on test que le message d'erreur est bien affiché
        $this->assertSelectorTextContains('.alert.alert-danger', 'Oops ! Vous devez être connecté pour supprimer une tâche.');
    }

    public function testDeleteTaskFailDisplayNotOwner(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email = 'user@user.fr';
        //titre de tache dont le user n'est pas l'auteur
        $title = 'Tâche de test bis';

        //on récupère le client connecté
        $this->createAuthenticatedClient($client, $email);
        //on récupère la tâche par le titre fourni
        $getTask = $this->getTaskByTitle($client, $title);
        //on va sur la page qui permet de supprimer la tâche et on est pas l'auteur de la tâche
        $client->request('GET', '/tasks/' . $getTask->getId() . '/delete');

        //on test la redirection vers la page des tâches
        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();
        //on test que le message d'erreur est bien affiché
        $this->assertSelectorTextContains('.alert.alert-danger', 'Oops ! Vous n\'êtes pas l\'auteur de cette tâche. Vous ne pouvez pas la supprimer');
    }

    public function testDeleteTaskValid(): void
    {   
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email='admin@todolist.com';
        $title = 'Test Titre';
        //on récupère le client connecté
        $this->createAuthenticatedClient($client, $email);
        //on récupère la tâche par le titre fourni
        $getTask = $this->getTaskByTitle($client, $title);
        //on va sur la page qui permet de supprimer la tâche et on est l'auteur de la tâche/ou admin
        $client->request('GET', '/tasks/' . $getTask->getId() . '/delete');

        //on test la redirection vers la page des tâches
        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();
        //on test que le message de succès est bien affiché
        $this->assertSelectorTextContains('.alert.alert-success', 'Superbe ! La tâche a bien été supprimée.');
    }
}
