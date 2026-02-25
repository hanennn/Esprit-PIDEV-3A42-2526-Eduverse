<?php
// src/Controller/Back/CertificationController.php

namespace App\Controller\Back;

use App\Entity\Certification;
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
use Dompdf\Dompdf;
use Dompdf\Options;

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

            // Générer le PDF de la certification
            return $this->redirectToRoute('front_certif_pdf', ['id' => $certification->getId()]);
        }

        return $this->render('back/quiz/edit.html.twig', [
            'form' => $form->createView(),
            'attempt' => $attempt,
        ]);
    }

    // Générer le PDF de la certification
    #[Route('/front/certification/{id}/pdf', name: 'front_certif_pdf')]
    public function generatePdf(Certification $certif): Response
    {
        // Configuration de Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        // Rendu du template PDF
        $html = $this->renderView('front/quiz/pdf.html.twig', [
            'certif' => $certif,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Télécharger le PDF directement
        $filename = 'certification-' . $certif->getId() . '.pdf';
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}
