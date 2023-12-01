<?php

namespace App\Tests\Controller;

use App\Tests\Trait\TestClientUtilitiesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{

    use TestClientUtilitiesTrait;
/***************** TEST FONCTION INDEXLIST() *******************/

    public function testUserIndexDisplayFailedNotAuth()
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        // on fait une requête HTTP sur l'URL "/users"
        $client->request('GET', '/users');
        //vu que pas connecté retour page login
        $this->assertResponseRedirects('/login');

    }
    public function testUserIndexDisplayFailedWhenRoleUser()
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email = 'user@user.fr';

        //on s'autentifie avec le role user
        $this->createAuthenticatedClient($client, $email);
        // on fait une requête HTTP sur l'URL "/users"
        $client->request('GET', '/users');
        //vu qu'on a pas le role admin on a une erreur 403
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

    }

    public function testUserIndexDisplaySuccessWhenRoleAdmin()
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email = 'admin@todolist.com';
        //on s'autentifie avec le role admin
        $this->createAuthenticatedClient($client, $email);
        // on fait une requête HTTP sur l'URL "/users"
        $client->request('GET', '/users');
        //on a une réponse 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK); // Ou un autre code d'état approprié

    }

    public function testUserIndexHasUsers(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email = 'admin@todolist.com';
        //on s'autentifie avec le role admin
        $this->createAuthenticatedClient($client, $email);
        // on fait une requête HTTP sur l'URL "/users"
        $crawler = $client->request('GET', '/users');

        //on compte le nombre de tâches affichées
        $userCount = $crawler->filter('.users-data')->count();

        //On test que la page affiche au moins 15 tâches
        $this->assertGreaterThanOrEqual(12, $userCount, "La page doit afficher au moins 12 users");
        //on test qu'on a un titre h1
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');

    }

/***************** TEST FONCTION CREATE() *******************/

    public function testUserCreateDisplayFailedNotAuth()
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        // on fait une requête HTTP sur l'URL "/users/create"
        $client->request('GET', '/users/create');
        //vu que pas connecté retour page login
        $this->assertResponseRedirects('/login');

    }

    public function testUserCreateDisplayFailedWhenRoleUser()
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email = 'user@user.fr';
        //on s'autentifie avec le role user
        $this->createAuthenticatedClient($client, $email);
        // on fait une requête HTTP sur l'URL "/users/create"
        $client->request('GET', '/users/create');
        //vu qu'on a pas le role admin on a une erreur 403
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

    }

    public function testUserCreateDisplaySuccessWhenRoleAdmin()
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email = 'admin@todolist.com';
        //on s'autentifie avec le role admin
        $this->createAuthenticatedClient($client, $email);
        // on fait une requête HTTP sur l'URL "/users/create"
        $client->request('GET', '/users/create');
        //on a une réponse 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        //on verifie qu'on a bien le bouton enregistrer et le titre h1
        $this->assertSelectorTextContains('button', 'Enregistrer');
        $this->assertSelectorTextContains('h1', 'Créer un utilisateur');

    }

    public function testUserCreationSuccessWhenRoleAdmin(): void
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email = 'admin@todolist.com';
        $emailUser = 'testFromUserControllerTest@example.com';

        //on verifie que l'utilisateur n'existe pas suite à un test précédent
        $existingUser = $this->getUserByEmail($client, $emailUser);
        if ($existingUser) {
            $this->deleteUserByEmail($client, $emailUser);
            var_dump('user supprimé');
        }
        //on s'autentifie avec le role admin
        $this->createAuthenticatedClient($client, $email);

        // on fait une requête HTTP sur l'URL "/users/create"
        $crawler = $client->request('GET', '/users/create');

        //on rempli le formulaire avec les données
        $form = $crawler->selectButton('Enregistrer')->form([
            'user_form[email]' => 'testFromUserControllerTest@example.com',
            'user_form[username]' => 'testControllerUserTest',
            'user_form[roles]' => 'ROLE_USER',
            'user_form[password][first]' => 'plaintextpassword',
            'user_form[password][second]' => 'plaintextpassword',
        ]);

        //on soumet le formulaire
        $client->submit($form);

        //on récupère l'utilisateur que l'on vient de créer
        $user = $this->getUserByEmail($client, $emailUser);

        //on verrifie qu'il a bien le role user
        $this->assertContains('ROLE_USER', $user->getRoles());

        //on verrifie qu'il a bien le bon username
        $this->assertStringContainsString('testControllerUserTest', $user->getUsername());

        //on verifie qu'il a bien le bon email
        $this->assertStringContainsString('testFromUserControllerTest@example.com', $user->getEmail());

        //on verifie que le mot de passe est bien hashé
        $this->assertNotEquals('plaintextpassword', $user->getPassword());

        //on verifie que le mot de passe est bien valide en utilisant le hasher
        $userPasswordHasher = $client->getContainer()->get('security.user_password_hasher');
        $this->assertTrue($userPasswordHasher->isPasswordValid($user, 'plaintextpassword'));

        //on est redirigé vers la page de liste des utilisateurs
        $this->assertResponseRedirects('/users');
        //on suit la redirection
        $client->followRedirect();
        //on test que la requete renvoie un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        //on test que le message de succès est bien affiché
        $this->assertSelectorTextContains('.alert.alert-success', 'Superbe ! l\'utilisateur a été créé avec succès.');

    }

    public function testUserCreationAdminFailed()
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email = 'admin@todolist.com';
        //on s'autentifie avec le role admin
        $this->createAuthenticatedClient($client, $email);

        // on fait une requête HTTP sur l'URL "/users/create"
        $crawler = $client->request('GET', '/users/create');

        //on rempli le formulaire avec les données à vide
        $form = $crawler->selectButton('Enregistrer')->form([
            'user_form[email]' => '',
            'user_form[username]' => '',
            'user_form[roles]' => 'ROLE_ADMIN',
            'user_form[password][first]' => '',
            'user_form[password][second]' => '',
        ]);
        //on soumet le formulaire
        $client->submit($form);
        //on test que la requete renvoie un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        //on test que le message d'erreur est bien affiché
        $this->assertSelectorTextContains('.alert-danger', 'Oops ! L\'utilisateur n\'a pas été créé. Veuillez recommencer votre saisie.');
    }
/***************** TEST FONCTION EDIT() *******************/

    public function testUserEditDisplayFailedNotAuth()
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email = 'user@user.fr';
        //on recupère l'utilisateur que l'on veut modifier
        $user = $this->getUserByEmail($client, $email);
        // on fait une requête HTTP sur l'URL "/users/{id}/edit"
        $client->request('GET', '/users/' . $user->getId() . '/edit');
        //vu que pas connecté retour page login
        $this->assertResponseRedirects('/login');
    }

    public function testUserEditDisplayFailedWhenRoleUser()
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email = 'user@user.fr';
        //on s'autentifie avec le role user
        $this->createAuthenticatedClient($client, $email);
        //on recupère l'utilisateur que l'on veut modifier
        $user = $this->getUserByEmail($client, $email);
        // on fait une requête HTTP sur l'URL "/users/{id}/edit"
        $client->request('GET', '/users/' . $user->getId() . '/edit');
        //vu qu'on a pas le role admin on a une erreur 403
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

    }

    public function testUserEditDisplaySuccessWhenRoleAdmin()
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email = 'admin@todolist.com';
        $emailGetUser = 'user@user.fr';
        //on s'autentifie avec le role admin
        $this->createAuthenticatedClient($client, $email);
        //on recupère l'utilisateur que l'on veut modifier
        $user = $this->getUserByEmail($client, $emailGetUser);
        // on fait une requête HTTP sur l'URL "/users/{id}/edit"
        $client->request('GET', '/users/' . $user->getId() . '/edit');
        //on a une réponse 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        //on verifie qu'on a bien le bouton enregistrer et le titre h1
        $this->assertSelectorTextContains('button', 'Enregistrer');
        $this->assertSelectorTextContains('h1', 'Modifier ' . $user->getUsername());
    }
    public function testUserEditSuccessWhenRoleAdmin()
    {
        // on crée un client qui va nous permettre de faire des requêtes HTTP
        $client = static::createClient();
        $email = 'admin@todolist.com';
        $emailGetUser = 'testFromUserControllerTest@example.com';
        $emailGetUserEdit = 'testFromUserControllerTest@example.comEdit';
        //on s'autentifie avec le role admin
        $this->createAuthenticatedClient($client, $email);
        //on recupère l'utilisateur que l'on veut modifier
        $user = $this->getUserByEmail($client, $emailGetUser);

        $existingUser = $this->getUserByEmail($client, $emailGetUserEdit);
        if ($existingUser) {
            $this->deleteUserByEmail($client, $emailGetUserEdit);
            var_dump('user edit supprimé');
        }

        // on fait une requête HTTP sur l'URL "/users/{id}/edit"
        $crawler = $client->request('GET', '/users/' . $user->getId() . '/edit');

        //on rempli le formulaire avec les données
        $form = $crawler->selectButton('Enregistrer')->form([
            'user_form[email]' => 'testFromUserControllerTest@example.comEdit',
            'user_form[username]' => 'testControllerUserTestEdit',
            'user_form[roles]' => 'ROLE_ADMIN', 
            'user_form[password][first]' => 'plaintextpasswordEdit', 
            'user_form[password][second]' => 'plaintextpasswordEdit',
        ]);
        //on soumet le formulaire
        $client->submit($form);


        $userAfterEdit = $this->getUserByEmail($client, $emailGetUserEdit);
        $this->assertNotEquals('testControllerUserTest', $userAfterEdit->getUsername());
        $this->assertNotEquals('testFromUserControllerTest@example.com', $userAfterEdit->getEmail());
        $this->assertNotEquals('plaintextpasswordEdit', $userAfterEdit->getPassword());
        $this->assertNotEquals('ROLE_USER', $userAfterEdit->getRoles());

    
        //on verfie que le mot de passe est bien hashé
        $this->assertNotEquals('plaintextpasswordEdit', $userAfterEdit->getPassword());
        // on verifie la correspondance du mot de passe avec le hasher
        $userPasswordHasher = $client->getContainer()->get('security.user_password_hasher');
        $this->assertTrue($userPasswordHasher->isPasswordValid($userAfterEdit, 'plaintextpasswordEdit'));

        //on est redirigé vers la page de liste des utilisateurs
        $this->assertResponseRedirects('/users');
        //on suit la redirection
        $client->followRedirect();
        //on test que la requete renvoie un code 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        //on test que le message de succès est bien affiché
        $this->assertSelectorTextContains('.alert.alert-success', 'Superbe ! Utilisateur mis à jour avec succès.');


    }

}
