<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use TypeError;

class TaskTest extends KernelTestCase
{

    public function getEntity(): Task
    {
        //retourne une task avec des valeurs par défaut
        return (new Task())
            ->setTitle('Titre de la tâche')
            ->setContent('Contenu de la tâche')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setIsDone(false);
    }
    public function testEntityIsValid()
    {
        self::bootKernel();
        $container = static::getContainer();
        $task = $this->getEntity();

        $user = new User();
        $task->setUser($user);
        $this->assertSame($user, $task->getUser());

        $errors = $container->get('validator')->validate($task);
        $this->assertCount(0, $errors);

    }

    public function testEntityEmptyNotNullableProperty()
    {
        $this->expectException(TypeError::class);

        self::bootKernel();
        $container = static::getContainer();
        $task = $this->getEntity();
        $task->setTitle(null);
        $task->setContent(null);
        $task->setIsDone(null);
        $task->setCreatedAt(null);
        $errors = $container->get('validator')->validate($task);
        $this->assertCount(3, $errors);
    }

    public function testEntityEmptyNullableProperty()
    {
        self::bootKernel();
        $container = static::getContainer();
        $task = $this->getEntity();
        $task->setUser(null);
        $errors = $container->get('validator')->validate($task);
        $this->assertCount(0, $errors);
    }

    public function testGetSetTitle()
    {
        $task = $this->getEntity();
        $this->assertSame('Titre de la tâche', $task->getTitle());
    }

    public function testGetSetContent()
    {
        $task = $this->getEntity();
        $this->assertSame('Contenu de la tâche', $task->getContent());
    }

    public function testGetSetIsDone()
    {
        $task = $this->getEntity();
        $this->assertSame(false, $task->isIsDone());
    }

    public function testGetSetCreatedAt()
    {
        $task = $this->getEntity();
        $date = new \DateTimeImmutable(); // Créez une seule instance ici
        $task->setCreatedAt($date); // Utilisez cette instance pour le setter
        $this->assertSame($date, $task->getCreatedAt()); // Et
    }

    public function testGetSetUser()
    {
        $task = $this->getEntity();
        $user = new User();
        $task->setUser($user);
        $this->assertSame($user, $task->getUser());
    }

 
    public function testGetId()
    {
        $task = $this->getEntity();
        $this->assertSame(null, $task->getId());
    }

}
