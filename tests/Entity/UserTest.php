<?php

namespace App\Entity\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\User;
use App\Entity\Task;
use TypeError;

class UserTest extends KernelTestCase
{
   
    /**
     * Crée une entité User.
     */
    public function getEntity(): User
    {
        return (new User())
            ->setEmail('test@example.com')
            ->setPassword('password')
            ->setUsername('username')
            ->setRoles(['ROLE_USER']);
    }

/**
     * Teste si l'entité User est valide.
     * On s'attend à ce qu'aucune erreur ne soit retournée par le validateur.
     * 
     */
    public function testEntityIsValid()
    {
        self::bootKernel();
        $container = static::getContainer();
        $user = $this->getEntity();

        $errors = $container->get('validator')->validate($user);
        $this->assertCount(0, $errors);

    }

    /**
     * Teste si l'entité User est invalide. 
     * On s'attend à ce que le validateur retourne 4 erreurs.
     * on teste les propriétés non nullables
     */
    public function testEntityIsInvalid()
    {
        $this->expectException(TypeError::class);
        self::bootKernel();
        $user = new User();
        $user->setEmail(null);
        $user->setPassword(null);
        $user->setUsername(null);
        $user->setRoles(null);
        $errors = $this->getContainer()->get('validator')->validate($user);
        $this->assertCount(4, $errors);
    }

    /**
     * On test les getter de l'entité User
     */
    public function testGetEmail()
    {
        $user = $this->getEntity();
        $this->assertSame('test@example.com', $user->getEmail());
    }

    public function testGetPassword()
    {
        $user = $this->getEntity();
        $this->assertSame('password', $user->getPassword());
    }

    public function testGetUsername()
    {
        $user = $this->getEntity();
        $this->assertSame('username', $user->getUsername());
    }

    public function testGetRoles()
    {
        $user = $this->getEntity();
        $user->setRoles(['ROLE_ADMIN']);
        $roles = $user->getRoles();
        $this->assertTrue(in_array('ROLE_ADMIN', $roles));
        $this->assertTrue(in_array('ROLE_USER', $roles)); 
    }
    public function testGetTasks()
    {
        // ici on crée un nouvel utilisateur et deux nouvelles tâches
        $user = new User();
        $task1 = new Task(); 
        $task2 = new Task();
    
        // on ajoute les tâches à l'utilisateur
        $user->addTask($task1);
        $user->addTask($task2);
        
        // on récupère les tâches de l'utilisateur
        $tasks = $user->getTasks();
        
        // on vérifie que le tableau contient bien deux tâches
        $this->assertCount(2, $tasks); 
        // on vérifie que les deux tasks sont bien contenu dans le tableau
        $this->assertTrue($tasks->contains($task1));
        $this->assertTrue($tasks->contains($task2)); 
    }
    
    public function testAddTask()
    {
        $user = new User();
        $task = new Task();
    
        $user->addTask($task);
    
        $this->assertCount(1, $user->getTasks()); 
        $this->assertTrue($user->getTasks()->contains($task));
        $this->assertSame($user, $task->getUser()); 
    }

    public function testRemoveTask()
    {
        $user = new User();
        $task = new Task();
    
        $user->addTask($task);
        $user->removeTask($task);
    
        $this->assertCount(0, $user->getTasks()); // Check if the task count is 0
        $this->assertFalse($user->getTasks()->contains($task)); // Check if the task is removed
        $this->assertNull($task->getUser()); // Optionally check if the task's user is set to null
    }


    public function testGetIdUser(){
        $user = $this->getEntity();
        $this->assertSame(null, $user->getId());

    }

    public function testUserIdentifier(){
        $email = 'test@example.com';
        $user = new User();
        $user->setEmail($email);
    
        $this->assertEquals($email, $user->getUserIdentifier());    
    }
    

}
