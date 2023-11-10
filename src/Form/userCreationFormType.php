<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class userCreationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'E-mail',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Renseignez un E-mail.',
                    ]),
                ],
            ])
            ->add('username', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Nom d\'utilisateur',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Renseignez un nom d\'utilisateur.',
                    ]),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle',
                'attr' => ['class' => 'form-control'],
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'expanded' => false, 
                'multiple' => false,
            ])

            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent correspondre.',
                'required' => true,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => ['class' => 'form-control'],
                ],
                'second_options' => [
                    'label' => 'Tapez le mot de passe à nouveau',
                    'attr' => ['class' => 'form-control'],
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 254,
                    ]),
                ],
            ])
        ;

        $builder->get('roles')
        //on ajoute un model transformer pour transformer les données de l'entité pour le formulaire
        //et on fait appel à la classe CallbackTransformer qui permet de transformer les données grace à deux fonctions anonymes
        //utile pour transformer les données de l'entité pour le formulaire et inversement quand elle ne correspondent pas
            ->addModelTransformer(new CallbackTransformer(
                /* Cette fonction transforme les données de l'entité pour le formulaire
            Prend en entrée un tableau, $rolesArray, qui représente les rôles de l'utilisateur*/
                function ($rolesArray) {
                    //Vérifie si $rolesArray est bien un tableau et qu'il contient au moins un élément
                    if (is_array($rolesArray) && count($rolesArray) > 0) {
                        // Si les conditions sont remplies, on retourne le premier élément du tableau. Car role unique
                        return $rolesArray[0];
                    }
                    // Si $rolesArray n'est pas un tableau ou est vide, on retourne null
                    return null;
                },
                // Cette fonction fait l'inverse de la première : elle transforme les données du formulaire pour l'entité
                //Prend en entrée une chaîne de caractères, qui représente le rôle sélectionné
                function ($rolesString) {
                    // Transforme la chaîne de caractères en tableau car l'entité attend un tableau de rôles.
                    return [$rolesString];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
