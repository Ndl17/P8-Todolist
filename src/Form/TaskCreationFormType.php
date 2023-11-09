<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TaskCreationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',null, [
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
