<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use TypeError;

class TaskTest extends KernelTestCase
{

    /**
     * Crée une entité Task.
     */
    public function getEntity(): Task
    {
        //retourne une task avec des valeurs par défaut
        return (new Task())
            ->setTitle('Titre de la tâche')
            ->setContent('Contenu de la tâche')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setIsDone(false);
    }

    /**
     * Teste si l'entité Task est valide.
     * On s'attend à ce qu'aucune erreur ne soit retournée par le validateur.
     * 
     */
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

    /**
     * Teste si l'entité Task est invalide. 
     * On s'attend à ce que le validateur retourne 3 erreurs.
     * on teste les propriétés non nullables
     */
    public function testEntityEmptyNotNullableProperty()
    {
        $this->expectException(TypeError::class);

       // self::bootKernel();
        $container = static::getContainer();
        $task = $this->getEntity();
        $task->setTitle(null);
        $task->setContent(null);
        $task->setIsDone(null);
        $task->setCreatedAt(null);
        $errors = $container->get('validator')->validate($task);
        $this->assertCount(3, $errors);
    }

    /**
     * Teste si l'entité Task est invalide. 
     * On s'attend à ce que le validateur retourne 0 erreur.
     * on teste la propriété nullable
     */
    public function testEntityEmptyNullableProperty()
    {
        self::bootKernel();
        $container = static::getContainer();
        $task = $this->getEntity();
        $task->setUser(null);
        $errors = $container->get('validator')->validate($task);
        $this->assertCount(0, $errors);
    }

    /**
     * On teste les getter de l'entité Task.
     */

    public function testGetTitle()
    {
        $task = $this->getEntity();
        $this->assertSame('Titre de la tâche', $task->getTitle());
    }

    public function testGetContent()
    {
        $task = $this->getEntity();
        $this->assertSame('Contenu de la tâche', $task->getContent());
    }

    public function testGetIsDone()
    {
        $task = $this->getEntity();
        $this->assertSame(false, $task->isIsDone());
    }

    public function testGetCreatedAt()
    {
        $task = $this->getEntity();
        $date = new \DateTimeImmutable(); 
        $task->setCreatedAt($date); 
        $this->assertSame($date, $task->getCreatedAt()); 
    }

    public function testSetUser()
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
