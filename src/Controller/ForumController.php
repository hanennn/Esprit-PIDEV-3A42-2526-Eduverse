<?php

namespace App\Controller;

use App\Entity\Sujet;
use App\Entity\Message;
use App\Entity\User;
use App\Form\SujetType;
use App\Form\MessageType;
use App\Service\HistoriqueLogger;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/forum')]
class ForumController extends AbstractController
{
    private NotificationService $notificationService;
    private HistoriqueLogger $historiqueLogger;

    public function __construct(NotificationService $notificationService, HistoriqueLogger $historiqueLogger)
    {
        $this->notificationService = $notificationService;
        $this->historiqueLogger = $historiqueLogger;
    }

    #[Route('/', name: 'app_forum_index')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'dateRecent');

        // Validate sort parameter
        $validSortFields = ['dateRecent', 'dateOld', 'titre'];
        if (!in_array($sort, $validSortFields, true)) {
            $sort = 'dateRecent';
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
        } elseif ($sort === 'dateOld') {
            $queryBuilder->orderBy('s.dateCreation', 'ASC');
        } else {
            // dateRecent - default
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
        if (!$user instanceof User) {
            $this->addFlash('warning', 'Vous devez être connecté pour créer un sujet.');
            return $this->redirectToRoute('app_login');
        }

        // Only teachers/formateurs can create topics
        if (!in_array('ROLE_TEACHER', $user->getRoles(), true)) {
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

            $this->historiqueLogger->log(
                'sujet',
                'create',
                $sujet->getId(),
                sprintf('Sujet #%d "%s" créé.', $sujet->getId(), $sujet->getTitre() ?? ''),
                $user
            );
            $entityManager->flush();

            // Notify all students about the new forum
            $this->notificationService->notifyNewForum($sujet);

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

        // Create form for adding messages only if user is logged in (et typé User)
        $form = null;
        if ($user instanceof User) {
            $message = new Message();
            $form = $this->createForm(MessageType::class, $message);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $message->setAuteur($user);
                $message->setSujet($sujet);
                $message->setDatePublication(new \DateTime());

                // Notify the teacher about the new comment
                $this->notificationService->notifyNewComment($message, $sujet);

                $entityManager->persist($message);
                $entityManager->flush();

                $this->historiqueLogger->log(
                    'message',
                    'create',
                    $message->getId(),
                    sprintf(
                        'Message #%d ajouté sur le sujet #%d "%s".',
                        $message->getId(),
                        $sujet->getId(),
                        $sujet->getTitre() ?? ''
                    ),
                    $user
                );
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
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Check if user is the author and a teacher, or admin
        if (
            !($sujet->getAuteur() === $user && in_array('ROLE_TEACHER', $user->getRoles(), true))
            && !in_array('ROLE_ADMIN', $user->getRoles(), true)
        ) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce sujet.');
        }

        $form = $this->createForm(SujetType::class, $sujet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->historiqueLogger->log(
                'sujet',
                'update',
                $sujet->getId(),
                sprintf('Sujet #%d "%s" modifié.', $sujet->getId(), $sujet->getTitre() ?? ''),
                $user
            );

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
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Check if user is the author and a teacher, or admin
        if (
            !($sujet->getAuteur() === $user && in_array('ROLE_TEACHER', $user->getRoles(), true))
            && !in_array('ROLE_ADMIN', $user->getRoles(), true)
        ) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce sujet.');
        }

        if ($this->isCsrfTokenValid('delete' . $sujet->getId(), (string) $request->request->get('_token'))) {
            $sujetId = $sujet->getId();
            $sujetTitle = $sujet->getTitre();

            $this->historiqueLogger->log(
                'sujet',
                'delete',
                $sujetId,
                sprintf('Sujet #%d "%s" supprimé.', $sujetId, $sujetTitle ?? ''),
                $user
            );

            foreach ($sujet->getMessages() as $message) {
                $entityManager->remove($message);
            }

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
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $sujet = $message->getSujet();
        if (!$sujet instanceof Sujet) {
            throw $this->createNotFoundException('Sujet not found');
        }

        // Check if user is the author or admin
        if ($message->getAuteur() !== $user && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce message.');
        }

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->historiqueLogger->log(
                'message',
                'update',
                $message->getId(),
                sprintf('Message #%d modifié dans le sujet "%s".', $message->getId(), $sujet->getTitre() ?? ''),
                $user
            );

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
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $sujet = $message->getSujet();
        if (!$sujet instanceof Sujet) {
            // Fix PHPStan + sécurité: un message doit appartenir à un sujet
            throw $this->createNotFoundException('Sujet not found');
        }

        $messageAuthor = $message->getAuteur();
        $sujetOwner = $sujet->getAuteur();

        // Check if user is the author, the sujet owner (teacher), or admin
        $isAuthor = $messageAuthor instanceof User && $messageAuthor->getId() === $user->getId();

        $isSujetOwner = $sujetOwner instanceof User
            && $sujetOwner->getId() === $user->getId()
            && in_array('ROLE_TEACHER', $user->getRoles(), true);

        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles(), true);

        if (!$isAuthor && !$isSujetOwner && !$isAdmin) {
            throw $this->createAccessDeniedException('You are not allowed to delete this message.');
        }

        if ($this->isCsrfTokenValid('delete' . $message->getId(), (string) $request->request->get('_token'))) {
            $messageId = $message->getId();
            $sujetId = $sujet->getId();
            $sujetTitle = $sujet->getTitre();

            $this->historiqueLogger->log(
                'message',
                'delete',
                $messageId,
                sprintf('Message #%d supprimé du sujet #%d "%s".', $messageId, $sujetId, $sujetTitle ?? ''),
                $user
            );

            // Notify the message author if a teacher deleted their comment (not if they deleted it themselves)
            if (!$isAuthor && $isSujetOwner) {
                if ($messageAuthor instanceof User) {
                    $this->notificationService->notifyCommentDeleted($messageAuthor, $sujet);
                }
            }

            $entityManager->remove($message);
            $entityManager->flush();

            $this->addFlash('success', 'Message supprimé avec succès!');
        }

        return $this->redirectToRoute('app_forum_show', ['id' => $sujet->getId()]);
    }
}