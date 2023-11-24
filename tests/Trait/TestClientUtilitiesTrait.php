<?php

namespace App\Tests\Trait;

use App\Entity\Task;
use App\Entity\User;

trait TestClientUtilitiesTrait
{
    public function createAuthenticatedClient($client, $email)
    {
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => $email]);
        $userLogged = $client->loginUser($testUser);
        return $userLogged;
    }

    public function getTask($client, $email)
    {
        $user = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class)->findOneBy(['email' => $email]);
        $taskRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        $task = $taskRepository->findOneBy(['user' => $user->getId()]);
        return $task;
    }

}
