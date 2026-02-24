<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/notification')]
#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    #[Route('/', name: 'app_notification_index')]
    public function index(NotificationRepository $notificationRepository): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $notifications = $notificationRepository->findByUser($user, 50);
        $unreadCount = $notificationRepository->countUnreadByUser($user);

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    #[Route('/unread-count', name: 'app_notification_unread_count')]
    public function unreadCount(NotificationRepository $notificationRepository): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['count' => 0]);
        }

        $count = $notificationRepository->countUnreadByUser($user);

        return new JsonResponse(['count' => $count]);
    }

    #[Route('/{id}/read', name: 'app_notification_mark_read', methods: ['POST'])]
    public function markAsRead(int $id, NotificationRepository $notificationRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $notification = $notificationRepository->find($id);
        
        if (!$notification) {
            return new JsonResponse(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $user = $this->getUser();
        
        if ($notification->getDestinataire() !== $user) {
            return new JsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->setIsRead(true);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/mark-all-read', name: 'app_notification_mark_all_read', methods: ['POST'])]
    public function markAllAsRead(NotificationRepository $notificationRepository): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $notificationRepository->markAllAsReadForUser($user);

        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/delete', name: 'app_notification_delete', methods: ['POST'])]
    public function delete(int $id, NotificationRepository $notificationRepository, EntityManagerInterface $entityManager): Response
    {
        $notification = $notificationRepository->find($id);
        
        if (!$notification) {
            throw $this->createNotFoundException('Notification not found');
        }

        $user = $this->getUser();
        
        if ($notification->getDestinataire() !== $user) {
            throw $this->createAccessDeniedException('You cannot delete this notification.');
        }

        $entityManager->remove($notification);
        $entityManager->flush();

        $this->addFlash('success', 'Notification supprimée avec succès!');

        return $this->redirectToRoute('app_notification_index');
    }
}
