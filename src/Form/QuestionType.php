<?php
namespace App\Form;

use App\Entity\Question;
use App\Form\DataTransformer\ReponsesTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class QuestionType extends AbstractType
{
    private ReponsesTransformer $transformer;

    public function __construct(ReponsesTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('question', TextType::class, ['label' => 'Question'])
            ->add('points', IntegerType::class, ['label' => 'Points'])
            ->add('reponses', TextareaType::class, [
                'label' => 'Réponses (JSON)',
                'attr' => [
                    'placeholder' => '[{"texte":"Réponse1","correct":true},{"texte":"Réponse2","correct":false}]',
                    'rows' => 5
                ]
            ]);

        // ⚡ On ajoute le DataTransformer pour gérer le JSON
        $builder->get('reponses')->addModelTransformer($this->transformer);
    }
}
