<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskCreationFormType;
use App\Repository\TaskRepository;
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
        }else{
            $this->addFlash('danger', 'La tâche n\'a pas été ajoutée. Veuillez recommencer votre saisie.');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

}
