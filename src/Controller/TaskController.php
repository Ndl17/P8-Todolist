<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskFormType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    /**
     * Affiche la liste des tâches.
     *
     * @param  mixed $taskRepository Le repository des tâches.
     * @return Response La réponse HTTP.
     */
    #[Route('/tasks', name: 'task_list')]    
    public function index(TaskRepository $taskRepository): Response
    {
        //récupère toutes les tâches grâce à la méthode  findby() avec un tri par ordre décroissant
        $tasks = $taskRepository->findBy([], ['id' => 'DESC'] );
        //retourne la vue list.html.twig en lui passant en paramètre le tableau de tâches
        return $this->render('task/index.html.twig', ['tasks' => $tasks]);
    }

     /**
     * Fonction pour créer une tâche.
     *
     * @param  mixed $entityManager Le manager de Doctrine.
     * @param  mixed $request La requête HTTP.
     * @param  mixed $security Le service Security pour récupérer l'utilisateur connecté.
     * @return Response La réponse HTTP.
     */
    #[Route('/tasks/create', name: 'task_create')]    
    public function create(EntityManagerInterface $entityManager, Request $request, Security $security): Response
    {
        //avec security on récupère l'utilisateur connecté
        $currentUser = $security->getUser();
        //création d'une nouvelle tâche
        $task = new Task();
        //on attribue l'utilisateur connecté à la tâche
        $task->setUser($currentUser);
        // on crée le formulaire grâce à la méthode createForm() du contrôleur
        $form = $this->createForm(TaskFormType::class, $task);
        //on récupère les données du formulaire
        $form->handleRequest($request);
        //Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            //on récupère la date de création de la tâche
            $createdAt = new \DateTimeImmutable();
            //on set la date de création de la tâche
            $task->setCreatedAt($createdAt);
            //on set la propriété isDone à false
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

    /**
     *Cette fonction permet d'éditer une tâche en fonction de son id.
     *
     * @param Task $task La tâche à éditer.
     * @param Request $request la requête HTTP.
     * @param EntityManagerInterface $entityManager le manager de Doctrine.
     * @param Security $security le service Security pour récupérer l'utilisateur connecté.
     * @return Response la réponse HTTP.
     */
    #[Route('/tasks/{id}/edit', name: 'task_edit')]
    public function edit(Task $task, Request $request, EntityManagerInterface $entityManager, Security $security):Response
    {
        //on récupère l'utilisateur connecté
        $user = $security->getUser();
        //si l'utilisateur n'est pas connecté
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour modifier une tâche.');
            return $this->redirectToRoute('task_list');
        }
        //création du formulaire grâce à la méthode createForm() du contrôleur
        $form = $this->createForm(TaskFormType::class, $task);
        //on récupère l'auteur initial de la tâche
        $initialAuthor = $task->getUser();
        //on récupère les données du formulaire
        $form->handleRequest($request);
        //si l'utilisateur connecté est admin ou si l'utilisateur connecté est l'auteur de la tâche
        if (in_array('ROLE_ADMIN', $user->getRoles()) || $user == $task->getUser()) {
            //si le formulaire est soumis et valide
            if ($form->isSubmitted() && $form->isValid()) {
                //on rattache l'auteur initial à la tâche qu'on est en train de modifier
                $task->setUser($initialAuthor);
                //on persiste la tâche et on la flush
                $entityManager->persist($task);
                $entityManager->flush();

                $this->addFlash('success', 'La tâche a bien été modifiée.');

                return $this->redirectToRoute('task_list');

            } else {
                $this->addFlash('danger', 'La tâche n\'a pas été modifiée. Veuillez recommencer votre saisie.');
            }
        } else {
            $this->addFlash('danger', 'Vous n\'êtes pas l\'auteur de cette tâche. Vous ne pouvez pas la modifier');
            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * Fonction qui permet de setter la propriété isDone à true ou false d'une tâche en fonction de son id
     *
     * @param  mixed $task tache dont on veut modifier la propriété isDone
     * @param  mixed $entityManager le manager de Doctrine
     * @param  mixed $security le service Security pour récupérer l'utilisateur connecté
     * @return Response la réponse HTTP
     */
    #[Route('/tasks/{id}/toggle', name: 'task_toggle')]    
    public function toggleTask(Task $task, EntityManagerInterface $entityManager, Security $security):Response
    {
        //on récupère l'utilisateur connecté
        $user = $security->getUser();

        //si l'utilisateur n'est pas connecté
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour modifier une tâche.');
            return $this->redirectToRoute('task_list');
        }

        //si l'utilisateur connecté est admin ou si l'utilisateur connecté est l'auteur de la tâche
        if (in_array('ROLE_ADMIN', $user->getRoles()) || $user == $task->getUser()) {
            //on inverse la valeur de la propriété isDone
            $task->setIsDone(!$task->isIsDone());
            
            //on persiste la tâche et on la flush
            $entityManager->persist($task);
            $entityManager->flush();

            if ($task->isIsDone()) {
                $this->addFlash('success', 'La tâche a bien été marquée comme faite.');
            } else {
                $this->addFlash('success', 'La tâche a bien été marquée comme non faite.');
            }

            return $this->redirectToRoute('task_list');

        } else {
            $this->addFlash('danger', 'Vous n\'êtes pas l\'auteur de cette tâche. Vous ne pouvez pas la modifier');
            return $this->redirectToRoute('task_list');
        }
    }

    /**
     * Fonction qui permet de supprimer une tâche en fonction de son id
     *
     * @param  mixed $task tache à supprimer
     * @param  mixed $entityManager le manager de Doctrine
     * @param  mixed $security le service Security pour récupérer l'utilisateur connecté
     * @return Response la réponse HTTP
     */
    #[Route('/tasks/{id}/delete', name: 'task_delete')]
    public function delete(Task $task, EntityManagerInterface $entityManager, Security $security):Response
    {
        //on récupère l'utilisateur connecté
        $user = $security->getUser();
        //si l'utilisateur n'est pas connecté
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour supprimer une tâche.');
            return $this->redirectToRoute('task_list');
        }
        //si l'utilisateur connecté est admin ou si l'utilisateur connecté est l'auteur de la tâche
        if (in_array('ROLE_ADMIN', $user->getRoles()) || $user == $task->getUser()) {
            //on supprime la tâche
            $entityManager->remove($task);
            $entityManager->flush();
            $this->addFlash('success', 'La tâche a bien été supprimée.');
            return $this->redirectToRoute('task_list');
        } else {
            $this->addFlash('danger', 'Vous n\'êtes pas l\'auteur de cette tâche. Vous ne pouvez pas la supprimer');
            return $this->redirectToRoute('task_list');
        }
    }

}
