<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Entity\Sujet;
use App\Entity\Message;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     * Notify student when their comment is deleted
     */
    public function notifyCommentDeleted(User $student, Sujet $sujet): void
    {
        $notification = new Notification();
        $notification->setDestinataire($student);
        $notification->setType('comment_deleted');
        $notification->setMessage(sprintf(
            'Votre commentaire sur le sujet "%s" a été supprimé par le formateur.',
            $sujet->getTitre()
        ));
        $notification->setSujet($sujet);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    /**
     * Notify all students when a teacher creates a new forum/sujet
     */
    public function notifyNewForum(Sujet $sujet): void
    {
        // Get all users
        $allUsers = $this->userRepository->findAll();
        $authorId = $sujet->getAuteur()->getId();
        
        foreach ($allUsers as $user) {
            // Skip the author
            if ($user->getId() === $authorId) {
                continue;
            }

            // Check if user is a student (not ROLE_TEACHER and not ROLE_ADMIN)
            $roles = $user->getRoles();
            $isTeacher = in_array('ROLE_TEACHER', $roles);
            $isAdmin = in_array('ROLE_ADMIN', $roles);
            
            // Notify all users except teachers and admins
            if (!$isTeacher && !$isAdmin) {
                $notification = new Notification();
                $notification->setDestinataire($user);
                $notification->setType('new_forum');
                $notification->setMessage(sprintf(
                    'Nouveau sujet du forum: "%s" par %s %s',
                    $sujet->getTitre(),
                    $sujet->getAuteur()->getPrenom(),
                    $sujet->getAuteur()->getNom()
                ));
                $notification->setSujet($sujet);

                $this->entityManager->persist($notification);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Notify teacher when a student comments on their post
     */
    public function notifyNewComment(Message $message, Sujet $sujet): void
    {
        $teacher = $sujet->getAuteur();
        $commenter = $message->getAuteur();

        // Don't notify if the teacher commented on their own post
        if ($teacher->getId() === $commenter->getId()) {
            return;
        }

        $notification = new Notification();
        $notification->setDestinataire($teacher);
        $notification->setType('new_comment');
        $notification->setMessage(sprintf(
            '%s %s a commenté sur votre sujet "%s"',
            $commenter->getPrenom(),
            $commenter->getNom(),
            $sujet->getTitre()
        ));
        $notification->setSujet($sujet);
        // Don't set message_ref to avoid cascade persist issues - sujet reference is sufficient

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
