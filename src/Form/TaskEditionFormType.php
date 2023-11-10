<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TaskEditionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',TextType::class, [
                'attr' => ['class'=>'form-control'],
                'label' => 'Titre',
                'constraints' => [
                  new NotBlank([
                    'message' => 'Renseignez un titre.',
                  ]),
                ],
              ])
            ->add('content',TextareaType::class, [
                'attr' => ['class'=>'form-control'],
                'label' => 'Contenu',
                'constraints' => [
                  new NotBlank([
                    'message' => 'Renseignez un contenu.',
                  ]),
                ],
              ])
              ->add('user', EntityType::class, [  // champ autheur de la tache en disabled car on ne veut pas le modifier
                'class' => User::class,
                'choice_label' => 'username',
                'disabled' => true,
                'placeholder' => 'Anonyme',
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
