<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Utilisateur créé avec succès.');
                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('danger', 'Des erreurs sont présentes dans le formulaire, veuillez corriger.');
            }
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Utilisateur mis à jour.');
                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('danger', 'Des erreurs sont présentes dans le formulaire, veuillez corriger.');
            }
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/delete', name: 'app_user_delete', methods: ['POST'])]
public function delete(Request $request, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser(); // Get the currently logged-in user
    
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
    
    // Verify password before deleting (optional but recommended)
    $password = $request->request->get('confirmPassword');
    // You can add password verification here if you want
    
    // Delete the user
    $entityManager->remove($user);
    $entityManager->flush();
    
    // Logout and redirect
    $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
    return $this->redirectToRoute('app_logout');
}


#[Route('/profile/update', name: 'app_instructor_update_profile', methods: ['POST'])]
public function updateProfile(Request $request, EntityManagerInterface $entityManager): Response
{
    /** @var User $user */
    $user = $this->getUser();

    // Update fields
    if ($request->request->has('nom')) {
        $user->setNom($request->request->get('nom'));
    }
    if ($request->request->has('prenom')) {
        $user->setPrenom($request->request->get('prenom'));
    }
    if ($request->request->has('email')) {
        $user->setEmail($request->request->get('email'));
    }
    if ($request->request->has('specialite')) {
        $user->setSpecialite($request->request->get('specialite'));
    }
    if ($request->request->has('experience')) {
        $user->setExperience($request->request->get('experience'));
    }
    
    // Handle password change
    if ($request->request->has('password') && !empty($request->request->get('password'))) {
        $hashedPassword = password_hash($request->request->get('password'), PASSWORD_BCRYPT);
        $user->setPassword($hashedPassword);
    }

    $entityManager->flush();

    $this->addFlash('success', 'Profil mis à jour avec succès !');

    return $this->redirectToRoute('app_profile');
}
}
