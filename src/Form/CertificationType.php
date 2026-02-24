<?php
// src/Form/CertificationType.php
namespace App\Form;

use App\Entity\Certification;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CertificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('badge', ChoiceType::class, [
                'choices' => [
                    'Bronze' => 'Bronze',
                    'Argent' => 'Argent',
                    'Or' => 'Or',
                    'Platine' => 'Platine',
                ],
                'label' => 'Badge',
            ])
            ->add('scoreObtenu', TextType::class, [
                'label' => 'Score obtenu',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Attribuer la certification',
                'attr' => ['class' => 'btn btn-success'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Certification::class,
        ]);
    }
}
