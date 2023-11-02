<?php

namespace App\DataFixtures;

use App\Entity\Task;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Faker\Factory;

class TaskFixtures extends Fixture implements OrderedFixtureInterface
{

    public function getOrder()
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

        //création tache sans user assigné
        for ($i = 0; $i < 5; $i++) {
            $task = new Task();
            $task->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $faker->date('Y-m-d H:i:s')));
            $task->setTitle($faker->sentence);
            $task->setContent($faker->realText());
            $task->setIsDone($faker->boolean);
            $manager->persist($task);
            $manager->flush();
        }
    }
}
