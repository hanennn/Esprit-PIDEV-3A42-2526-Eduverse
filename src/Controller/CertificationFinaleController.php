<?php

namespace App\Controller;

use App\Entity\Certification;
use App\Entity\CertificationFinale;
use App\Form\CertificationFinaleType;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class CertificationFinaleController extends AbstractController
{
    #[Route('/back/certification-finale/add/{id}', name: 'back_certification_finale_add')]
    public function addCertificationFinale(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        $certification = $em->getRepository(Certification::class)->find($id);

        if (!$certification) {
            $this->addFlash('danger', "Tentative de certification non trouvée.");
            return $this->redirectToRoute('back_certification_finale_add');
        }

        $certificationFinale = new CertificationFinale();
        $certificationFinale->setTentative($certification);
        $certificationFinale->setUser($certification->getUser());
        $certificationFinale->setQuiz($certification->getQuiz());

        $form = $this->createForm(CertificationFinaleType::class, $certificationFinale);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($certificationFinale);
            $em->flush();

            $this->sendCertificationEmail($certificationFinale, $mailer);

            $this->addFlash('success', "Certification finale ajoutée avec succès.");
            return $this->redirectToRoute('back_certification_finale_add', ['id' => $certification->getId()]);
        }

        return $this->render('add.html.twig', [
            'form' => $form->createView(),
            'certification' => $certification
        ]);
    }

private function sendCertificationEmail(CertificationFinale $certificationFinale, MailerInterface $mailer): void
{
    $user = $certificationFinale->getUser();

    $email = (new TemplatedEmail())
        ->from('eduversee2026@gmail.com')        // must match the Gmail account in DSN
        ->to($user->getEmail())
        ->subject('Votre Certification Finale')
        ->htmlTemplate('certification_finale.html.twig')
        ->context([
            'user' => $user,
            'certificationFinale' => $certificationFinale,
        ]);

    $mailer->send($email);
}

    /**
     * ✅ Route publique : scan QR -> ouvre cette page (avec résumé IA)
     */
    #[Route('/certificat/verifier/{id}/{token}', name: 'certification_finale_verify', methods: ['GET'])]
    public function verify(
        int $id,
        string $token,
        EntityManagerInterface $em,
        HttpClientInterface $httpClient
    ): Response {
        $cert = $em->getRepository(CertificationFinale::class)->find($id);

        if (!$cert) {
            return $this->render('certification_verify.html.twig', [
                'certification' => null,
                'aiSummary' => null,
            ]);
        }

        $expectedToken = $this->makeToken($cert);

        if (!hash_equals($expectedToken, $token)) {
            return $this->render('certification_verify.html.twig', [
                'certification' => null,
                'aiSummary' => null,
            ]);
        }

        // ✅ Résumé IA généré à la volée (sans DB)
        $aiSummary = $this->generateAiSummary($cert, $httpClient);

        return $this->render('certification_verify.html.twig', [
            'certification' => $cert,
            'aiSummary' => $aiSummary,
        ]);
    }

    private function makeToken(CertificationFinale $cert): string
    {
        // Mets APP_CERT_SECRET dans .env
        $secret = $_ENV['APP_CERT_SECRET'] ?? '';

        $data = $cert->getId() . '|' .
            $cert->getUser()->getEmail() . '|' .
            $cert->getDateEmission()->format('Y-m-d H:i:s');

        return hash_hmac('sha256', $data, $secret);
    }

    /**
     * ✅ IA summary (Groq) - sans DB
     */
    private function generateAiSummary(CertificationFinale $cert, HttpClientInterface $httpClient): ?string
    {
        $apiKey = $_ENV['GROQ_API_KEY'] ?? null;
        $model  = $_ENV['GROQ_MODEL'] ?? 'llama-3.1-8b-instant';

        if (!$apiKey) return null;

        $name = $cert->getUser()->getPrenom() . ' ' . $cert->getUser()->getNom();
        $quiz = $cert->getQuiz()->getTitre();
        $score = $cert->getTentative()?->getScoreObtenu();
        $badge = $cert->getTentative()?->getBadge();
        $date = $cert->getDateEmission()->format('d/m/Y');

        $prompt =
            "Rédige un résumé officiel professionnel en français (2 à 3 phrases max) pour un certificat de réussite.\n" .
            "Nom: $name\n" .
            "Quiz: $quiz\n" .
            "Score: $score/20\n" .
            "Badge: $badge\n" .
            "Date: $date\n" .
            "Contraintes: sans emoji, ton administratif, crédible, clair.";

        try {
            $res = $httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'Tu écris des résumés officiels de certificats.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 160,
                ],
            ]);

            $data = $res->toArray(false);
            $text = trim($data['choices'][0]['message']['content'] ?? '');

            return $text !== '' ? $text : null;

        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * PDF (avec QR) - ton code, juste nettoyé
     */
    #[Route('/back/quiz/{id}/generate-pdf', name: 'back_certification_finale_pdf')]
    public function generatePdf(int $id, EntityManagerInterface $em): Response
    {
        $certificationFinale = $em->getRepository(CertificationFinale::class)
            ->findOneBy(['quiz' => $id]);

        if (!$certificationFinale) {
            throw $this->createNotFoundException("Certification finale non trouvée pour ce quiz.");
        }

        $token = $this->makeToken($certificationFinale);

        // ✅ IMPORTANT: ici tu dois mettre IP de ton PC sur le WiFi (pas 127.0.0.1)
        $baseUrl = 'http://172.21.0.144:8000';

        $path = $this->generateUrl('certification_finale_verify', [
            'id' => $certificationFinale->getId(),
            'token' => $token
        ]);

        $verifyUrl = $baseUrl . $path;

        $qrCode = QrCode::create($verifyUrl)->setSize(220)->setMargin(10);
        $writer = new PngWriter();
        $qrBase64 = base64_encode($writer->write($qrCode)->getString());

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $html = $this->renderView('pdf.html.twig', [
            'certificationFinale' => $certificationFinale,
            'verifyUrl' => $verifyUrl,
            'qrBase64' => $qrBase64,
            'token' => $token,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'certification_finale_' . $certificationFinale->getId() . '.pdf';

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}
