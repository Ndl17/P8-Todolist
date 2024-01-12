<?php

namespace App\DataFixtures;

use App\Entity\Task;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TaskFixtures extends Fixture implements OrderedFixtureInterface
{

    public function getOrder(): int
    {
        return 2;
    }
    public function load(ObjectManager $manager): void
    {

        //création tache avec un user assigné
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $task = new Task();
            $task->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $faker->date('Y-m-d H:i:s')));
            $task->setTitle($faker->sentence);
            $task->setContent($faker->realText());
            $task->setIsDone($faker->boolean);
            $task->setUser($this->getReference('user_' . $i));
            $manager->persist($task);
            $manager->flush();
        }

        //on relie les tâche non assigné à l'utilisateur anonyme
        $anonymousUser = $this->getReference('user_anonymous');
        for ($i = 0; $i < 5; $i++) {
            $task = new Task();
            $task->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $faker->date('Y-m-d H:i:s')));
            $task->setTitle($faker->sentence);
            $task->setContent($faker->realText());
            $task->setIsDone($faker->boolean);
            $task->setUser($anonymousUser);
            $manager->persist($task);
            $manager->flush();
        }

        $taskToUserTest = new Task();
        $taskToUserTest->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $faker->date('Y-m-d H:i:s')));
        $taskToUserTest->setTitle('Tâche de test');
        $taskToUserTest->setContent('Contenu de la tâche de test');
        $taskToUserTest->setIsDone(0);
        $taskToUserTest->setUser($this->getReference('user_test'));
        $manager->persist($taskToUserTest);
        $manager->flush();

        $taskToUserTest = new Task();
        $taskToUserTest->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $faker->date('Y-m-d H:i:s')));
        $taskToUserTest->setTitle('Tâche de test 2');
        $taskToUserTest->setContent('Contenu de la tâche de test');
        $taskToUserTest->setIsDone(0);
        $taskToUserTest->setUser($this->getReference('user_test'));
        $manager->persist($taskToUserTest);
        $manager->flush();

        $taskToUserTestBis = new Task();
        $taskToUserTestBis->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $faker->date('Y-m-d H:i:s')));
        $taskToUserTestBis->setTitle('Tâche de test bis');
        $taskToUserTestBis->setContent('Contenu de la tâche de test bis');
        $taskToUserTestBis->setIsDone(0);
        $taskToUserTestBis->setUser($this->getReference('user_test_bis'));
        $manager->persist($taskToUserTestBis);
        $manager->flush();

    }
}
