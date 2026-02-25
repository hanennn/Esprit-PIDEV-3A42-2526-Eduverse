<?php

namespace App\Form;

use App\Entity\EventInscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventInscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('note', TextareaType::class, [
                'label' => 'Note / Message (optionnel)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ajoutez un message ou une question pour les organisateurs...',
                    'rows' => 4,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventInscription::class,
        ]);
    }
}
