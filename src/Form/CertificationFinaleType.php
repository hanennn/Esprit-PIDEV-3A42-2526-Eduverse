<?php

// src/Form/CertificationFinaleType.php

namespace App\Form;

use App\Entity\CertificationFinale;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CertificationFinaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateEmission', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date d\'émission'
            ])
            ->add('badge', ChoiceType::class, [
                'choices' => [
                    'Bronze' => 'Bronze',
                    'Argent' => 'Argent',
                    'Or' => 'Or',
                    'Platine' => 'Platine'
                ],
                'label' => 'Choisir un badge'
            ]);
           
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CertificationFinale::class,
        ]);
    }
}