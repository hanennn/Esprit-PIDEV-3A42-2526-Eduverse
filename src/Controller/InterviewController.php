<?php

namespace App\Controller;

use App\Entity\AnalyseInterview;
use App\Entity\DemandeBourse;
use App\Service\InterviewAnalyseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InterviewController extends AbstractController
{
    #[Route('/bourse/interview/{id}', name: 'bourse_interview')]
    public function index(DemandeBourse $demande, InterviewAnalyseService $analyseService): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($demande->getEtudiant() !== $user) {
            throw $this->createAccessDeniedException('Accès interdit.');
        }

        // Match exact status used in BourseAdminController
        if ($demande->getStatut() !== 'Accepté') {
            $this->addFlash('error', "L'interview n'est disponible que pour les demandes acceptées.");
            return $this->redirectToRoute('app_mes_candidatures');
        }

        if ($demande->getAnalyseInterview() !== null) {
            $this->addFlash('info', "Vous avez déjà passé l'interview pour cette bourse.");
            return $this->redirectToRoute('app_mes_candidatures');
        }

        // Check if AI microservice is reachable
        $aiAvailable = $analyseService->isAvailable();

        return $this->render('bourse/interview.html.twig', [
            'demande' => $demande,
            'aiAvailable' => $aiAvailable,
        ]);
    }

    #[Route('/bourse/interview/{id}/analyser', name: 'bourse_interview_analyser', methods: ['POST'])]
    public function analyser(
        Request $request,
        DemandeBourse $demande,
        InterviewAnalyseService $analyseService,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user || $demande->getEtudiant() !== $user) {
            return $this->json(['error' => 'Accès interdit.'], 403);
        }

        if ($demande->getStatut() !== 'Accepté') {
            return $this->json(['error' => 'Demande non acceptée.'], 400);
        }

        if ($demande->getAnalyseInterview() !== null) {
            return $this->json(['error' => 'Interview déjà réalisée.'], 400);
        }

        $audioFile = $request->files->get('audio');
        if (!$audioFile) {
            return $this->json(['error' => 'Aucun fichier audio reçu.'], 400);
        }

        // Validate file type
        $mimeType = $audioFile->getMimeType();
        $allowedMimes = ['audio/webm', 'audio/ogg', 'audio/wav', 'audio/mpeg', 'video/webm'];
        if (!in_array($mimeType, $allowedMimes)) {
            return $this->json(['error' => 'Format audio non supporté.'], 400);
        }

        $nomFichier = uniqid('interview_') . '.webm';
        $dossier = $this->getParameter('interviews_dir');
        $audioFile->move($dossier, $nomFichier);
        $cheminComplet = $dossier . '/' . $nomFichier;

        try {
            $resultat = $analyseService->analyser($cheminComplet);

            $analyse = new AnalyseInterview();
            $analyse->setDemandeBourse($demande);
            $analyse->setTranscription($resultat['transcription'] ?? null);
            $analyse->setScoreDetermine($resultat['scores_emotions']['déterminé'] ?? null);
            $analyse->setScoreAnxieux($resultat['scores_emotions']['anxieux'] ?? null);
            $analyse->setScoreConfiant($resultat['scores_emotions']['confiant'] ?? null);
            $analyse->setScoreMotive($resultat['scores_emotions']['motivé'] ?? null);
            $analyse->setScoreHesitant($resultat['scores_emotions']['hésitant'] ?? null);
            $analyse->setDebitParole($resultat['features_audio']['debit_mots_par_min'] ?? null);
            $analyse->setTauxHesitations($resultat['features_audio']['taux_hesitations_pct'] ?? null);
            $analyse->setEnergieVocale($resultat['features_audio']['energie_vocale'] ?? null);
            $analyse->setProfilGlobal($resultat['profil_global'] ?? null);
            $analyse->setRecommandation($resultat['recommandation'] ?? null);
            $analyse->setCheminAudio($nomFichier);
            $analyse->setDateInterview(new \DateTime());

            $em->persist($analyse);
            $em->flush();

            return $this->json([
                'success'       => true,
                'profil'        => $resultat['profil_global'],
                'transcription' => $resultat['transcription'],
                'scores'        => $resultat['scores_emotions'],
                'features'      => $resultat['features_audio'],
                'recommandation' => $resultat['recommandation'],
            ]);

        } catch (\Exception $e) {
            // Clean up the audio file on failure
            if (file_exists($cheminComplet)) {
                unlink($cheminComplet);
            }

            return $this->json([
                'error' => 'Le service d\'analyse IA est temporairement indisponible. Veuillez réessayer dans quelques minutes.'
            ], 503);
        }
    }
}
