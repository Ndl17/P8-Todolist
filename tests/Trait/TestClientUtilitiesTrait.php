<?php

namespace App\Tests\Trait;

use App\Entity\Task;
use App\Entity\User;

trait TestClientUtilitiesTrait
{
    public function createAuthenticatedClient($client, $email)
    {   
        //on fait appel au repository pour récupérer l'utilisateur grâce à son email et on le connecte
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => $email]);
        $userLogged = $client->loginUser($testUser);
        return $userLogged;
    }

    public function getTaskByUserId($client, $email)
    {   
        //on fait appel au repository pour récupérer l'utilisateur grâce à son email, et on récupère la tâche grâce à l'id de l'utilisateur
        $user = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class)->findOneBy(['email' => $email]);
        $taskRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        $task = $taskRepository->findOneBy(['user' => $user->getId()]);
        return $task;
    }

    public function getTaskByTitle($client, $title)
    {
        //on fait appel au repository pour récupérer la tâche grâce à son titre
        $taskRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        $task = $taskRepository->findOneBy(['title' => $title]);
        return $task;
    }

    public function getUserByEmail($client, $email)
    {
        //on fait appel au repository pour récupérer l'utilisateur grâce à son email
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $email]);
        return $user;
    }

    public function deleteUserByEmail($client, $email)
    {
        //on fait appel au repository pour récupérer l'utilisateur grâce à son email et on le supprime
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $email]);
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->remove($user);
        $entityManager->flush();
    }

}
