<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email',
                'required' => false,
            ])
            ->add('Username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'required' => false,
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe',
                'required' => false,
            ])
            ->add('accountType', ChoiceType::class, [
                'mapped' => false,
                'label' => 'Type de compte',
                'choices' => [
                    'Étudiant' => 'ROLE_STUDENT',
                    'Formateur' => 'ROLE_TEACHER',
                ],
                'data' => 'ROLE_STUDENT',
                'required' => false,
            ])
            ->add('specialite', TextType::class, [
                'label' => 'Spécialité',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: Développement Web, Marketing Digital...'
                ]
            ])
            ->add('experience', TextareaType::class, [
                'label' => 'Expérience',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Décrivez brièvement votre expérience professionnelle et vos qualifications...'
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J\'accepte les conditions',
                'required' => false,
            ])
            ->add('newsletter', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Newsletter',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}