<?php

namespace App\Controller;

use App\Entity\Sujet;
use App\Entity\Message;
use App\Form\SujetType;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/forum')]
class ForumController extends AbstractController
{
    #[Route('/', name: 'app_forum_index')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'dateCreation');
        
        // Validate sort parameter
        $validSortFields = ['dateCreation', 'titre'];
        if (!in_array($sort, $validSortFields)) {
            $sort = 'dateCreation';
        }

        $queryBuilder = $entityManager->getRepository(Sujet::class)->createQueryBuilder('s')
            ->leftJoin('s.auteur', 'u');

        // Apply search filter
        if (!empty($search)) {
            $queryBuilder
                ->where('s.titre LIKE :search OR s.contenu LIKE :search OR u.prenom LIKE :search OR u.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Apply sorting
        if ($sort === 'titre') {
            $queryBuilder->orderBy('s.titre', 'ASC');
        } else {
            $queryBuilder->orderBy('s.dateCreation', 'DESC');
        }

        $sujets = $queryBuilder->getQuery()->getResult();

        return $this->render('forum/index.html.twig', [
            'sujets' => $sujets,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    #[Route('/new', name: 'app_forum_new')]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour créer un sujet.');
            return $this->redirectToRoute('app_login');
        }

        // Only teachers/formateurs can create topics
        if (!in_array('ROLE_TEACHER', $user->getRoles())) {
            $this->addFlash('danger', 'Seuls les formateurs peuvent créer des sujets.');
            return $this->redirectToRoute('app_forum_index');
        }

        $sujet = new Sujet();
        $form = $this->createForm(SujetType::class, $sujet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sujet->setAuteur($user);
            $sujet->setDateCreation(new \DateTime());

            $entityManager->persist($sujet);
            $entityManager->flush();

            $this->addFlash('success', 'Sujet créé avec succès!');

            return $this->redirectToRoute('app_forum_show', ['id' => $sujet->getId()]);
        }

        return $this->render('forum/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_forum_show', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function show(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $sujet = $entityManager->getRepository(Sujet::class)->find($id);
        
        if (!$sujet) {
            throw $this->createNotFoundException('Sujet not found');
        }

        $user = $this->getUser();
        
        // Create form for adding messages only if user is logged in
        $form = null;
        if ($user) {
            $message = new Message();
            $form = $this->createForm(MessageType::class, $message);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $message->setAuteur($user);
                $message->setSujet($sujet);
                $message->setDatePublication(new \DateTime());

                $entityManager->persist($message);
                $entityManager->flush();

                $this->addFlash('success', 'Message posté avec succès!');

                return $this->redirectToRoute('app_forum_show', ['id' => $sujet->getId()]);
            }
        }

        return $this->render('forum/show.html.twig', [
            'sujet' => $sujet,
            'form' => $form?->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_forum_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $sujet = $entityManager->getRepository(Sujet::class)->find($id);
        
        if (!$sujet) {
            throw $this->createNotFoundException('Sujet not found');
        }

        $user = $this->getUser();
        
        // Check if user is the author and a teacher, or admin
        if (!($sujet->getAuteur() === $user && in_array('ROLE_TEACHER', $user->getRoles())) && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce sujet.');
        }

        $form = $this->createForm(SujetType::class, $sujet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Sujet updated successfully!');

            return $this->redirectToRoute('app_forum_show', ['id' => $sujet->getId()]);
        }

        return $this->render('forum/edit.html.twig', [
            'sujet' => $sujet,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_forum_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $sujet = $entityManager->getRepository(Sujet::class)->find($id);
        
        if (!$sujet) {
            throw $this->createNotFoundException('Sujet not found');
        }

        $user = $this->getUser();
        
        // Check if user is the author and a teacher, or admin
        if (!($sujet->getAuteur() === $user && in_array('ROLE_TEACHER', $user->getRoles())) && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce sujet.');
        }

        if ($this->isCsrfTokenValid('delete'.$sujet->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sujet);
            $entityManager->flush();

            $this->addFlash('success', 'Sujet supprimé avec succès!');
        }

        return $this->redirectToRoute('app_forum_index');
    }

    #[Route('/message/{id}/edit', name: 'app_forum_message_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function editMessage(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $message = $entityManager->getRepository(Message::class)->find($id);
        
        if (!$message) {
            throw $this->createNotFoundException('Message not found');
        }

        $user = $this->getUser();
        $sujet = $message->getSujet();
        
        // Check if user is the author or admin
        if ($message->getAuteur() !== $user && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce message.');
        }

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Message modifié avec succès!');
            return $this->redirectToRoute('app_forum_show', ['id' => $sujet->getId()]);
        }

        return $this->render('forum/edit_message.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
            'sujet' => $sujet,
        ]);
    }

    #[Route('/message/{id}/delete', name: 'app_forum_message_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteMessage(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $message = $entityManager->getRepository(Message::class)->find($id);
        
        if (!$message) {
            throw $this->createNotFoundException('Message not found');
        }

        $user = $this->getUser();
        $sujet = $message->getSujet();
        
        // Check if user is the author or admin
        if ($message->getAuteur() !== $user && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException('You are not allowed to delete this message.');
        }

        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $entityManager->remove($message);
            $entityManager->flush();

            $this->addFlash('success', 'Message supprimé avec succès!');
        }

        return $this->redirectToRoute('app_forum_show', ['id' => $sujet->getId()]);
    }
}
