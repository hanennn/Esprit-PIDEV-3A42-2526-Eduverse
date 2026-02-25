<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the account type from the form
            $accountType = $form->get('accountType')->getData();
            
            // Set the role based on account type
            $user->setRoles([$accountType]);
            
            // Set the registration date
            $user->setDateInscription(new \DateTimeImmutable());
            
            // Hash the password
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $plainPassword
                )
            );

            // Save the user to the database
            $entityManager->persist($user);
            $entityManager->flush();

            // Add a success message
            $this->addFlash('success', 'Compte créé avec succès!');

            return $this->redirectToRoute('app_register');
        }

        // Debug: show form errors if submitted but not valid
        if ($form->isSubmitted() && !$form->isValid()) {
            dump($form->getErrors(true, false));
        }

        return $this->render('sign_up.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}