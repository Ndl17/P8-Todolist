<?php

namespace App\Controller;

use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    #[Route('/task', name: 'task_list')]
    public function index(TaskRepository $taskRepository): Response
    {
        //récupère toutes les tâches grâce à la méthode findAll()
        $tasks = $taskRepository->findAll();
        //retourne la vue list.html.twig en lui passant en paramètre le tableau de tâches
        return $this->render('task/index.html.twig', ['tasks' => $tasks]);
    }
}
