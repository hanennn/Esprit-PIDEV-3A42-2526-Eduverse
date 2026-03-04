<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\NewPasswordType;
use App\Form\ResetPasswordRequestType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    #[Route('', name: 'app_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer, UserRepository $userRepository): Response
    {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            // Do not reveal whether a user account was found or not.
            if ($user) {
                $expiresAt = new \DateTimeImmutable('+1 hour');
                
                // Generate a signed URL
                // We include the password hash in the signature so that if the user changes 
                // their password (or requests another reset which changes the hash... wait, no, requesting doesn't change hash. 
                // But changing password does. So this makes it single-use w.r.t successful resets).
                $secret = $this->getParameter('kernel.secret');
                if (!is_string($secret)) {
                    throw new \RuntimeException('kernel.secret must be a string');
                }
                $signature = hash_hmac('sha256', $user->getId() . $user->getEmail() . $user->getPassword() . $expiresAt->getTimestamp(), $secret);
                $resetToken = [
                    'id' => $user->getId(),
                    'expires' => $expiresAt->getTimestamp(),
                    'signature' => $signature
                ];

                $resetUrl = $this->generateUrl('app_reset_password', $resetToken, UrlGeneratorInterface::ABSOLUTE_URL);

                $emailMessage = (new TemplatedEmail())
                    ->from(new Address('eduversee2026@gmail.com', 'Eduverse Security'))
                    ->to((string) $user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->htmlTemplate('reset_password/email.html.twig')
                    ->context([
                        'resetUrl' => $resetUrl,
                        'expiresAt' => $expiresAt,
                    ])
                ;

                $mailer->send($emailMessage);
            }

            $this->addFlash('success', 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/reset/{id}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, UserRepository $userRepository, int $id): Response
    {
        // 1. Validate parameters
        $expires = $request->query->get('expires');
        $signature = $request->query->get('signature');

        if (!$expires || !$signature) {
            $this->addFlash('danger', 'Lien invalide.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        // 2. Check expiration
        if (time() > $expires) {
            $this->addFlash('danger', 'Le lien a expiré.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        // 3. Fetch user
        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('danger', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        // 4. Verify signature
        // Recalculate signature with current user data
        $secret = $this->getParameter('kernel.secret');
        if (!is_string($secret)) {
            throw new \RuntimeException('kernel.secret must be a string');
        }
        $expectedSignature = hash_hmac('sha256', $user->getId() . $user->getEmail() . $user->getPassword() . $expires, $secret);
        if (!hash_equals($expectedSignature, (string) $signature)) {
            $this->addFlash('danger', 'Le lien est invalide ou a déjà été utilisé.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        // 5. Handle form
        $form = $this->createForm(NewPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode(hash) the plain password, and set it.
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $entityManager->flush();

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    private function cleanSessionAfterReset(): void
    {
        // Any specific session cleanup if needed
    }
}