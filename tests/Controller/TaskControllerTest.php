<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Tests\Trait\TestClientUtilitiesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{

    use TestClientUtilitiesTrait;

/***************** TEST FONCTION INDEX() *******************/
    public function testIndexTaskDisplayPage(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        // on demande au client de requêter une URL
        $client->request('GET', '/task');
        //on test que la requete renvoie un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

    }

    public function testIndexHasTask(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/task');
        //on compte le nombre de tâches affichées
        $taskCount = $crawler->filter('.card-task')->count();
        //On test que la page affiche au moins 15 tâches
        $this->assertGreaterThanOrEqual(15, $taskCount, "La page doit afficher au moins 15 tâches");
    }

/***************** TEST FONCTION CREATE() *******************/

    public function testCreateTaskDisplayPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/task/create');
        //on test que la requete renvoie un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        //on test que le formulaire contient bien le bouton ajouter
        $this->assertSelectorTextContains('button', 'Ajouter');
    }

    public function testCreateValidTask(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/task/create');
        //on remplit le formulaire
        $form = $crawler->selectButton('Ajouter')->form([
            'task_form[title]' => 'Test Titre',
            'task_form[content]' => 'Test Contenu',
        ]);
        //on soumet le formulaire
        $client->submit($form);
        //on test que la requete renvoie un code 302
        $this->assertResponseRedirects('/task');
        //on suit la redirection
        $client->followRedirect();
        //on test que la requete renvoie un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        //on test que le message de succès est bien affiché
        $this->assertSelectorTextContains('.alert.alert-success', 'La tâche a été bien été ajoutée.');
    }

    public function testCreateInvalidTask(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/task/create');
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
        $client = static::createClient();
        $email = 'user@user.fr';
        $this->createAuthenticatedClient($client, $email);

        $getTask = $this->getTask($client, $email);
        $client->request('GET', '/task');

        // Expected <h5> content
        $h5Titles = $client->getCrawler()->filter('h5.card-title');
        $expectedTitle = $getTask->getTitle();
        $titleFound = false;

        foreach ($h5Titles as $h5Title) {
            if ($h5Title->textContent === $expectedTitle) {
                $titleFound = true;
                break;
            }
        }

        $this->assertTrue($titleFound, "Le titre attendu n'a pas été trouvé dans les éléments h5.");

        $crawler = $client->request('GET', '/tasks/' . $getTask->getId() . '/edit');
        //on test que la requete renvoie un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        //on test que le formulaire contient bien le bouton modifier
        $this->assertSelectorTextContains('button', 'Ajouter');

    }

    public function testEditTaskFailDisplayNotConnected(): void
    {
        $client = static::createClient();
        $taskRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);

        $getTask = $taskRepository->findOneBy(['title' => 'Tâche de test']);
        $client->request('GET', '/tasks/' . $getTask->getId() . '/edit');

        $this->assertResponseRedirects('/task');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-danger', 'Oops ! Vous devez être connecté pour modifier une tâche.');
    }

    public function testEditTaskFailDisplayNotOwner(): void
    {
        $client = static::createClient();
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'user@user.fr']);
        $client->loginUser($testUser);

        $taskRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        $getTask = $taskRepository->findOneBy(['title' => 'Tâche de test bis']);
        $client->request('GET', '/tasks/' . $getTask->getId() . '/edit');

        $this->assertResponseRedirects('/task');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-danger', 'Oops ! Vous n\'êtes pas l\'auteur de cette tâche. Vous ne pouvez pas la modifier');
    }

    public function testEditTaskValid(): void
    {
        $client = static::createClient();
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'user@user.fr']);
        $client->loginUser($testUser);
        $taskRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        $getTask = $taskRepository->findOneBy(['user' => $testUser]);

        $crawler = $client->request('GET', '/tasks/' . $getTask->getId() . '/edit');
        //  dd($crawler);
        $form = $crawler->selectButton('Ajouter')->form([
            'task_form[title]' => 'Tâche de test',
            'task_form[content]' => 'Contenu de la tâche de test',
        ]);
        //on soumet le formulaire
        $client->submit($form);
        //on test que la requete renvoie un code 302
        $this->assertResponseRedirects('/task');
        //on suit la redirection
        $client->followRedirect();
        //on test que la requete renvoie un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        //on test que le message de succès est bien affiché
        $this->assertSelectorTextContains('.alert.alert-success', 'Superbe ! La tâche a bien été modifiée.');

    }

    public function testEditTaskInvalid(): void
    {
        $client = static::createClient();

        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'user@user.fr']);
        $client->loginUser($testUser);
        $taskRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        $getTask = $taskRepository->findOneBy(['user' => $testUser]);

        $crawler = $client->request('GET', '/tasks/' . $getTask->getId() . '/edit');
        //  dd($crawler);
        $form = $crawler->selectButton('Ajouter')->form([
            'task_form[title]' => '',
            'task_form[content]' => '',
        ]);

        //on soumet le formulaire
        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

/***************** TEST FONCTION TOGGLE() *******************/

    public function testToggleTaskFailDisplayNotConnected(): void
    {
        $client = static::createClient();
        $taskRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);

        $getTask = $taskRepository->findOneBy(['title' => 'Tâche de test']);
        $client->request('GET', '/tasks/' . $getTask->getId() . '/toggle');

        $this->assertResponseRedirects('/task');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-danger', 'Oops ! Vous devez être connecté pour modifier une tâche.');
    }

    public function testToggleTaskFailDisplayNotOwner(): void
    {
        $client = static::createClient();
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'user@user.fr']);
        $client->loginUser($testUser);

        $taskRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        $getTask = $taskRepository->findOneBy(['title' => 'Tâche de test bis']);
        $client->request('GET', '/tasks/' . $getTask->getId() . '/toggle');

        $this->assertResponseRedirects('/task');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-danger', 'Oops ! Vous n\'êtes pas l\'auteur de cette tâche. Vous ne pouvez pas la modifier');

    }

    public function testToggleTaskValid(): void
    {
        $client = static::createClient();
        //    $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'user@user.fr']);
        $client->loginUser($testUser);
        $taskRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        $getTask = $taskRepository->findOneBy(['user' => $testUser]);

        $initialIsDone = $getTask->isIsDone();

        // Toggle the task
        $crawler = $client->request('GET', '/tasks/' . $getTask->getId() . '/toggle');

        // Assert redirection
        $this->assertResponseRedirects('/task');
        $client->followRedirect();
        $afterIsDone = $getTask->isIsDone();

        $this->assertNotEquals($initialIsDone, $afterIsDone, 'Le statut de la tâche soit changer.');

        // Assert success response and success message
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        if ($afterIsDone == false) {
            $this->assertSelectorTextContains('.alert.alert-success', 'Superbe ! La tâche a bien été marquée comme non faite.');
        } else {
            $this->assertSelectorTextContains('.alert.alert-success', 'Superbe ! La tâche a bien été marquée comme faite.');
        }
    }

    public function testToggleTaskValidValue(): void
    {
        $client = static::createClient();
        //    $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'user@user.fr']);
        $client->loginUser($testUser);
        $taskRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        $getTask = $taskRepository->findOneBy(['user' => $testUser]);

        $initialIsDone = $getTask->isIsDone();

        // Toggle the task
        $crawler = $client->request('GET', '/tasks/' . $getTask->getId() . '/toggle');

        // Assert redirection
        $this->assertResponseRedirects('/task');
        $client->followRedirect();
        $afterIsDone = $getTask->isIsDone();

        if ($afterIsDone == false) {
            $this->assertEquals($afterIsDone, false, 'Le statut de la tâche doit être false.');
        } else {
            $this->assertEquals($afterIsDone, true, 'Le statut de la tâche doit être true.');
        }
    }

    public function testToggleTaskInvalidNonExistentTask(): void
    {
        $client = static::createClient();
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'user@user.fr']);
        $client->loginUser($testUser);

        // Tenter de basculer une tâche avec un ID inexistant
        $client->request('GET', '/tasks/999999/toggle');

        // Vérifier que la réponse indique que la tâche n'existe pas
        $this->assertResponseStatusCodeSame(404); 
    }

/***************** TEST FONCTION DELETE() *******************/

    public function testDeleteTaskFailDisplayNotConnected(): void
    {
        $client = static::createClient();
        $taskRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);

        $getTask = $taskRepository->findOneBy(['title' => 'Tâche de test']);
        $client->request('GET', '/tasks/' . $getTask->getId() . '/delete');

        $this->assertResponseRedirects('/task');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-danger', 'Oops ! Vous devez être connecté pour supprimer une tâche.');
    }

    public function testDeleteTaskFailDisplayNotOwner(): void
    {
        $client = static::createClient();
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'user@user.fr']);
        $client->loginUser($testUser);

        $taskRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        $getTask = $taskRepository->findOneBy(['title' => 'Tâche de test bis']);
        $client->request('GET', '/tasks/' . $getTask->getId() . '/delete');

        $this->assertResponseRedirects('/task');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-danger', 'Oops ! Vous n\'êtes pas l\'auteur de cette tâche. Vous ne pouvez pas la supprimer');
    }

    public function testDeleteTaskValid(): void
    {
        $client = static::createClient();
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'admin@todolist.com']);
        $client->loginUser($testUser);

        $taskRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        $getTask = $taskRepository->findOneBy(['title' => 'Test Titre']);
        $client->request('GET', '/tasks/' . $getTask->getId() . '/delete');

        $this->assertResponseRedirects('/task');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-success', 'Superbe ! La tâche a bien été supprimée.');
    }
}
