<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class userCreationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'attr' => ['class'=>'form-control'],
            'label' => 'E-mail',
            'constraints' => [
              new NotBlank([
                'message' => 'Renseignez un E-mail.',
              ]),
            ],
          ])
          ->add('username',TextType::class, [
            'attr' => ['class'=>'form-control'],
            'label' => 'Nom d\'utilisateur',
            'constraints' => [
              new NotBlank([
                'message' => 'Renseignez un nom d\'utilisateur.',
              ]),
            ],
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
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
