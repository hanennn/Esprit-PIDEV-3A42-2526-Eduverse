<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, [
                'label' => 'Question',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La question est obligatoire.']),
                ],
            ])
            ->add('points', IntegerType::class, [
                'label' => 'Points',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Les points sont obligatoires.']),
                    new Assert\Positive(['message' => 'Les points doivent être positifs.']),
                ],
            ])
            ->add('reponses', TextareaType::class, [
                'label' => 'Réponses (JSON)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '[{"texte":"Réponse1","correct":true},{"texte":"Réponse2","correct":false}]',
                    'rows' => 5
                ],
                'help' => 'Format JSON: tableau avec "texte" et "correct" (true/false)',
            ]);

        // Transform array to JSON string for display, and JSON string back to array for storage
        $builder->get('reponses')
            ->addModelTransformer(new CallbackTransformer(
                function ($reponsesAsArray) {
                    // Transform array to JSON string for the form
                    if (empty($reponsesAsArray)) {
                        return '';
                    }
                    return json_encode($reponsesAsArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                },
                function ($reponsesAsString) {
                    // Transform JSON string back to array for the entity
                    if (empty($reponsesAsString)) {
                        return [];
                    }
                    $decoded = json_decode($reponsesAsString, true);
                    return $decoded ?? [];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
