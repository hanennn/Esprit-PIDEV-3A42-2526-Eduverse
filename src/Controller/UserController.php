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


#[Route('/export-csv', name: 'app_user_export_csv', methods: ['GET'])]
public function exportCsv(UserRepository $userRepository): Response
{
    $users = $userRepository->findAll();

    $csvContent = "ID,Nom,Prenom,Email,Username,Role,Statut,Date Inscription\n";

    foreach ($users as $user) {
        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles) || in_array('ROLE_SUPER_ADMIN', $roles)) {
            $role = 'Admin';
        } elseif (in_array('ROLE_TEACHER', $roles)) {
            $role = 'Formateur';
        } else {
            $role = 'Etudiant';
        }

        $csvContent .= sprintf(
            "%d,%s,%s,%s,%s,%s,%s,%s\n",
            $user->getId(),
            $user->getNom(),
            $user->getPrenom(),
            $user->getEmail(),
            $user->getUsername(),
            $role,
            $user->isActive() ? 'Actif' : 'Inactif',
            $user->getDateInscription() ? $user->getDateInscription()->format('d/m/Y') : 'N/A'
        );
    }

    $response = new Response($csvContent);
    $response->headers->set('Content-Type', 'application/octet-stream');
    $response->headers->set('Content-Disposition', 'attachment; filename="utilisateurs_' . date('Y-m-d') . '.csv"');
    $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Expires', '0');

    return $response;
}
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

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/delete-admin', name: 'app_user_delete_admin', methods: ['POST'], requirements: ['id' => '\d+'])]
public function deleteAdmin(Request $request, User $user, EntityManagerInterface $entityManager): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    if (!$this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->request->get('_token'))) {
        $this->addFlash('error', 'Token invalide.');
        return $this->redirectToRoute('app_profile');
    }

    $entityManager->remove($user);
    $entityManager->flush();

    $this->addFlash('success', 'Compte supprimé avec succès.');
    return $this->redirectToRoute('app_profile');
}
    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
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


#[Route('/{id}/toggle-active', name: 'app_user_toggle_active', methods: ['POST'], requirements: ['id' => '\d+'])]
public function toggleActive(Request $request, User $user, EntityManagerInterface $entityManager): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    if (!$this->isCsrfTokenValid('toggle_active_' . $user->getId(), $request->request->get('_token'))) {
        $this->addFlash('error', 'Token invalide.');
        return $this->redirectToRoute('app_profile');
    }

    $user->setIsActive(!$user->isActive());
    $entityManager->flush();

    $status = $user->isActive() ? 'activé' : 'désactivé';
    $this->addFlash('success', 'Le compte de ' . $user->getNom() . ' ' . $user->getPrenom() . ' a été ' . $status . '.');

    return $this->redirectToRoute('app_profile');
}


#[Route('/profile/update', name: 'app_instructor_update_profile', methods: ['POST'])]
public function updateProfile(Request $request, EntityManagerInterface $entityManager): Response
{
    /** @var User $user */
    $user = $this->getUser();

    // Update fields
    if ($request->request->has('nom')) {
            $parts = explode(' ', trim($request->request->get('nom')), 2);
            $user->setNom($parts[0] ?? '');
            $user->setPrenom($parts[1] ?? '');
        }
    if ($request->request->has('email')) {
        $user->setEmail($request->request->get('email'));
    }
    if ($request->request->has('specialite')) {
        $user->setSpecialite($request->request->get('specialite'));
        
    }
    if ($request->request->has('username')) {
        $user->setUsername($request->request->get('username'));
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
