<?php

namespace App\Form;

use App\Entity\Chapitres;
use App\Entity\Cours;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ChapitresType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titreChap', TextType::class, [
                'label' => 'Titre du chapitre',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Entrez le titre du chapitre',
                    'maxlength' => 255
                ]
            ])
            ->add('descChap', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Entrez la description (min. 10 caractères)',
                    'rows' => 4
                ]
            ])
            ->add('ordreChap', IntegerType::class, [
                'label' => 'Ordre',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Numéro d\'ordre du chapitre',
                    'min' => 1
                ]
            ])
            ->add('dureeChap', TextType::class, [
                'label' => 'Durée',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: 45 minutes, 1h30'
                ]
            ])
            ->add('statutChap', ChoiceType::class, [
                'label' => 'Statut',
                'required' => true,
                'choices' => [
                    'Ouvert' => 'Ouvert',
                    'Non ouvert' => 'Non ouvert'
                ],
                'data' => 'Non ouvert', 
                'attr' => [
                    'class' => 'form-select'
                    
                ]
            ])
           
            ->add('contenuChap', FileType::class, [
                'label' => 'Contenu (fichier)',
                'required' => false,
                'mapped' => false, // Important: not directly mapped to entity
                'attr' => [
                    'accept' => '.pdf,.mp4,.avi,.mov,.mkv'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '100M',
                        'mimeTypes' => [
                            'application/pdf',
                            'video/mp4',
                            'video/x-msvideo',
                            'video/quicktime',
                            'video/x-matroska'
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier PDF ou vidéo valide',
                    ])
                ],
                'help' => 'Formats acceptés: PDF, MP4, AVI, MOV, MKV (max 100MB)'
            ])
            ->add('typeContenu', ChoiceType::class, [
                'label' => 'Type de contenu',
                'required' => true,
                'choices' => [
                    'PDF' => 'pdf',
                    'Vidéo' => 'vidéo'
                ],
                'placeholder' => 'Sélectionnez le type',
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            // Commented out as per your Twig template
            // ->add('cours', EntityType::class, [
            //     'class' => Cours::class,
            //     'choice_label' => 'titre', // or whatever field name for course title
            //     'label' => 'Cours',
            //     'required' => true,
            //     'placeholder' => 'Choisissez un cours'
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chapitres::class,
        ]);
    }
}