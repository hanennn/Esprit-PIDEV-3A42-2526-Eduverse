<?php

namespace App\Form;

use App\Entity\Quiz;
use App\Entity\Cours;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints as Assert;
class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre du quiz',
                'attr' => ['class' => 'form-control'],
            ])

            ->add('typeQuiz', ChoiceType::class, [
                'choices' => [
                    'Intermédiaire' => 'Intermédiaire',
                    'Final' => 'Final',
                ],
                'placeholder' => 'Choisir un type',
                'label' => 'Type de quiz',
                'attr' => ['class' => 'form-control'],
            ])

            ->add('duree', IntegerType::class, [
    'label' => 'Durée (minutes)',
    'attr' => ['class' => 'form-control'],
    'constraints' => [
        new Assert\NotBlank(['message' => 'La durée est obligatoire.']),
        new Assert\Positive(['message' => 'La durée doit être positive.']),
    ],
])

->add('scoreMinimum', IntegerType::class, [
    'label' => 'Score minimum',
    'attr' => ['class' => 'form-control'],
    'constraints' => [
        new Assert\NotBlank(['message' => 'Le score minimum est obligatoire.']),
        new Assert\PositiveOrZero(['message' => 'Le score minimum doit être positif ou nul.']),
    ],
])

            ->add('coursAssocie', EntityType::class, [
                'class' => Cours::class,
                'choice_label' => 'titre_cours',
                'placeholder' => 'Sélectionnez un cours',
                'label' => 'Cours associé',
                'attr' => ['class' => 'form-control'],
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Créer quiz',
                'attr' => ['class' => 'btn btn-success mt-3'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
        ]);
    }
}

