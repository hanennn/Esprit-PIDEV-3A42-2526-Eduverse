<?php
// src/Controller/Back/CertificationController.php

namespace App\Controller;

use App\Entity\Certification;
use App\Repository\CertificationRepository;
use App\Entity\Quiz;
use App\Entity\User;
use App\Form\CertificationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


#[Route('/back/certification')]
class CertificationController extends AbstractController
{
    
    // Route pour attribuer la certification
    #[Route('/create/{id}', name: 'back_certification_create')]
    public function assignCertification(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $attempt = $em->getRepository(Certification::class)->find($id);
        if (!$attempt || $attempt->getStatut() !== 'Réussi') {
            $this->addFlash('danger', "Tentative échouée ou certification déjà attribuée.");
            return $this->redirectToRoute('back_quiz_index');
        }

        // Création du formulaire pour attribuer la certification
        $certification = new Certification();
        $form = $this->createForm(CertificationType::class, $certification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Remplir les informations de certification
            $certification->setUser($attempt->getUser());
            $certification->setQuiz($attempt->getQuiz());
            $certification->setDateAttribution(new \DateTime());
            $certification->setStatut('Validée'); // Statut valide
            $certification->setScoreObtenu($attempt->getScoreObtenu());

            // Enregistrer la certification
            $em->persist($certification);
            $em->flush();

            // Ajouter un message flash
            $this->addFlash('success', "Certification attribuée avec succès !");

          
            return $this->redirectToRoute('back_quiz_index');
        }

        return $this->render('back/quiz/edit.html.twig', [
            'form' => $form->createView(),
            'attempt' => $attempt,
        ]);
    }
   
     // Route pour afficher les tentatives récentes
    
   
}
