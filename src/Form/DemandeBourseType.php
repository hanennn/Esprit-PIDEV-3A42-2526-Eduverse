<?php

namespace App\Form;

use App\Entity\DemandeBourse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class DemandeBourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('niveauEtudes', ChoiceType::class, [
                'label' => 'Niveau d\'études',
                'choices'  => [
                    'Licence' => 'Licence',
                    'Master' => 'Master',
                    'Doctorat' => 'Doctorat',
                    'Ingénierie' => 'Ingénierie',
                    'Autre' => 'Autre',
                ],
                'attr' => ['class' => 'form-control', 'style' => 'height: auto; padding: 10px;'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un niveau d\'études.']),
                ],
            ])
            ->add('lettreMotivation', FileType::class, [
                'label' => 'Lettre de motivation (PDF)',
                'mapped' => false,
                'required' => true,
                'attr' => ['class' => 'form-control-file'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez télécharger votre lettre de motivation.']),
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide.',
                    ])
                ],
            ])
            ->add('note', TextareaType::class, [
                'label' => 'Note / Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Ajoutez des informations complémentaires sur votre candidature...'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeBourse::class,
        ]);
    }
}
