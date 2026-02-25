<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('texte', TextType::class, [
                'label' => 'Texte de la réponse'
            ])
            ->add('correct', CheckboxType::class, [
                'label' => 'Correct',
                'required' => false
            ]);
    }
}
