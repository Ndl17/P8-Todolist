<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskCreationFormType;
use App\Form\TaskEditionFormType;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/task/create', name: 'task_create')]
    public function create(EntityManagerInterface $entityManager, Request $request, Security $security)
    {
        //avec security on récupère l'utilisateur connecté
        $currentUser = $security->getUser();
        //création d'une nouvelle tâche
        $task = new Task();
        //on attribue l'utilisateur connecté à la tâche
        $task->setUser($currentUser);
        //on récupère la date de création de la tâche
        $createdAt = new \DateTimeImmutable();
        //on set la date de création de la tâche
        $task->setCreatedAt($createdAt);
        // on crée le formulaire grâce à la méthode createForm() du contrôleur
        $form = $this->createForm(TaskCreationFormType::class, $task);
        //on récupère les données du formulaire
        $form->handleRequest($request);
        //Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            //on set la date de création de la tâche, l'utilisateur connecté et on set la tâche à non terminée
            // $task->setCreatedAt(new \DateTime());
            $task->setUser($this->getUser());
            $task->setIsDone(false);
            //on persiste la tâche et on la flush
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        } else {
            $this->addFlash('danger', 'La tâche n\'a pas été ajoutée. Veuillez recommencer votre saisie.');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit')]
    public function edit(Task $task, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        //création du formulaire grâce à la méthode createForm() du contrôleur
        $form = $this->createForm(TaskEditionFormType::class, $task);
        //on récupère les données du formulaire
        $form->handleRequest($request);
        //si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            //on persiste la tâche et on la flush
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        } else {
            $this->addFlash('danger', 'La tâche n\'a pas été modifiée. Veuillez recommencer votre saisie.');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }
    
    #[Route('/tasks/{id}/toggle', name: 'task_toggle')]
    public function toggleTask(Task $task, EntityManagerInterface $entityManager)
    {
        //on inverse la valeur de la propriété isDone
        $task->setIsDone(!$task->isIsDone());
        //on persiste la tâche et on la flush
        $entityManager->persist($task);
        $entityManager->flush();
        if ($task->isIsDone()) {
            $this->addFlash('success', 'La tâche a bien été marquée comme faite.', $task->getTitle());
        } else {
            $this->addFlash('success', 'La tâche a bien été marquée comme non faite.', $task->getTitle());
        }
        return $this->redirectToRoute('task_list');

    }

}
