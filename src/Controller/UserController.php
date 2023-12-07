<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_list')]
    public function indexList(UserRepository $userRepository): Response
    {
        //récupère toutes les user grâce à la méthode findAll()
        $users = $userRepository->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/create', name: 'user_create')]
    public function create(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(userFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'l\'utilisateur a été créé avec succès.');

            return $this->redirectToRoute('user_list');
        } else {
            $this->addFlash('danger', 'L\'utilisateur n\'a pas été créé. Veuillez recommencer votre saisie.');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/{id}/edit', name: 'user_edit')]
    public function edit(User $user, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        //on crée le formulaire grâce à la méthode createForm() du contrôleur et on lui passe en paramètre le type de formulaire et l'instance de l'utilisateur
        $form = $this->createForm(UserFormType::class, $user);

        //on récupère les données du formulaire
        $form->handleRequest($request);
        //si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            //pour changer le mot de passe, on va créer le mot de passe de l'utilisateur et on va l'encoder
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));
            //on persiste l'utilisateur et on le flush
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');

            return $this->redirectToRoute('user_list');
        } else {
            $this->addFlash('danger', 'L\'utilisateur n\'a pas été mis à jour. Veuillez recommencer votre saisie.');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

}
