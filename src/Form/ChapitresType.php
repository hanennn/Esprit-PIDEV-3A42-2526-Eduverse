<?php

namespace App\Form;

use App\Entity\Chapitres;
use App\Entity\Cours;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ChapitresType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titreChap', TextType::class, [
                'property_path' => 'titre_chap',
                'required' => false,
                'empty_data' => '', 
            ])
            ->add('descChap', TextType::class, [
                'property_path' => 'desc_chap',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('ordreChap', IntegerType::class, [
                'property_path' => 'ordre_chap',
                'required' => false,
                'empty_data' => null,
            ])
            ->add('dureeChap', TextType::class, [
                'property_path' => 'duree_chap',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('statutChap', ChoiceType::class, [
                'choices' => [
                    'Ouvert' => 'Ouvert',
                    'Non ouvert' => 'Non ouvert',
                    
                ],
                'placeholder' => 'Sélectionnez un statut',
                'required' => false,
                
            ])
            
            ->add('resumeChap', TextType::class, [
                'property_path' => 'resume_chap',
                'empty_data' => '',
                'required' => false,
            ])
                
            ->add('contenuChap', FileType::class, [
                'property_path' => 'contenu_chap',
                'mapped' => false,
                'required' => false,
            ])
            ->add('typeContenu', ChoiceType::class, [
                'choices' => [
                    'PDF' => 'pdf',
                    'Vidéo' => 'vidéo',
                    
                ],
                'placeholder' => 'Sélectionnez un type',
                'required' => false,
                
            ]);
           
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chapitres::class,
        ]);
    }
}
