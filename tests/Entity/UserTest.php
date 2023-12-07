<?php

namespace App\Entity\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\User;
use App\Entity\Task;
use TypeError;

class UserTest extends KernelTestCase
{
   
    public function getEntity(): User
    {
        return (new User())
            ->setEmail('test@example.com')
            ->setPassword('password')
            ->setUsername('username')
            ->setRoles(['ROLE_USER']);
    }


    public function testEntityIsValid()
    {
        self::bootKernel();
        $container = static::getContainer();
        $user = $this->getEntity();

        $errors = $container->get('validator')->validate($user);
        $this->assertCount(0, $errors);

    }

    public function testEntityIsInvalid()
    {
        $this->expectException(\TypeError::class);
        $user = new User();
        $user->setEmail(null);
        $user->setPassword(null);
        $user->setUsername(null);
        $user->setRoles(null);
        $errors = $this->getContainer()->get('validator')->validate($user);
        $this->assertCount(4, $errors);
    }

    public function testGetSetEmail()
    {
        $user = $this->getEntity();
        $this->assertSame('test@example.com', $user->getEmail());
    }

    public function testGetSetPassword()
    {
        $user = $this->getEntity();
        $this->assertSame('password', $user->getPassword());
    }

    public function testGetSetUsername()
    {
        $user = $this->getEntity();
        $this->assertSame('username', $user->getUsername());
    }

    public function testGetSetRoles()
    {
        $user = $this->getEntity();
        $user->setRoles(['ROLE_ADMIN']);
        $roles = $user->getRoles();
        $this->assertTrue(in_array('ROLE_ADMIN', $roles));
        $this->assertTrue(in_array('ROLE_USER', $roles)); 
    }
    public function testGetTasks()
    {
        $user = new User();
        $task1 = new Task(); // Assume Task is another entity that you've defined
        $task2 = new Task();
    
        $user->addTask($task1);
        $user->addTask($task2);
    
        $tasks = $user->getTasks();
    
        $this->assertCount(2, $tasks); // Check if two tasks are returned
        $this->assertTrue($tasks->contains($task1)); // Check if first task is in the collection
        $this->assertTrue($tasks->contains($task2)); // Check if second task is in the collection
    }
    
    public function testAddTask()
    {
        $user = new User();
        $task = new Task();
    
        $user->addTask($task);
    
        $this->assertCount(1, $user->getTasks()); // Check if the task count is 1
        $this->assertTrue($user->getTasks()->contains($task)); // Check if the task is added
        $this->assertSame($user, $task->getUser()); // Optionally check if the task's user is set correctly
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
