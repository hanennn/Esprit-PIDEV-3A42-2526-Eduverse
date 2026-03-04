<?php

namespace App\Controller;

use App\Entity\Bourse;
use App\Repository\BourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/bourse')]
class BourseController extends AbstractController
{
    #[Route('/', name: 'app_bourse_list', methods: ['GET'])]
    public function index(BourseRepository $bourseRepository): Response
    {
        // Fetch valid bourses (dateFin > now)
        $bourses = $bourseRepository->createQueryBuilder('b')
            ->where('b.dateFin > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('b.dateAttribution', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('bourse/index.html.twig', [
            'bourses' => $bourses,
        ]);
    }

    #[Route('/detailsetudiantBourse/{id}', name: 'app_bourse_details_student', methods: ['GET'])]
    public function show(Bourse $bourse, \Doctrine\ORM\EntityManagerInterface $entityManager): Response
    {
        $alreadyApplied = false;
        $user = $this->getUser();
        
        if ($user) {
            $existingDemande = $entityManager->getRepository(\App\Entity\DemandeBourse::class)->findOneBy([
                'etudiant' => $user,
                'bourse' => $bourse
            ]);
            $alreadyApplied = $existingDemande !== null;
        }

        return $this->render('bourse/show_public.html.twig', [
            'bourse' => $bourse,
            'alreadyApplied' => $alreadyApplied,
        ]);
    }

    #[Route('/{id}/postuler', name: 'app_bourse_apply', methods: ['GET', 'POST'])]
    public function apply(
        \Symfony\Component\HttpFoundation\Request $request, 
        Bourse $bourse, 
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \Symfony\Component\String\Slugger\SluggerInterface $slugger
    ): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour postuler.');
            return $this->redirectToRoute('app_login');
        }

        // Check if already applied
        $existingDemande = $entityManager->getRepository(\App\Entity\DemandeBourse::class)->findOneBy([
            'etudiant' => $user,
            'bourse' => $bourse
        ]);

        if ($existingDemande) {
            $this->addFlash('warning', 'Vous avez déjà postulé à cette bourse.');
            return $this->redirectToRoute('app_bourse_details_student', ['id' => $bourse->getId()]);
        }

        $demande = new \App\Entity\DemandeBourse();
        $demande->setBourse($bourse);
        $demande->setEtudiant($user);

        $form = $this->createForm(\App\Form\DemandeBourseType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile|null $lettreFile */
            $lettreFile = $form->get('lettreMotivation')->getData();

            if (null !== $lettreFile) {
                $originalFilename = pathinfo($lettreFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$lettreFile->guessExtension();

                try {
                    $projectDir = $this->getParameter('kernel.project_dir');
                    if (!is_string($projectDir)) {
                        throw new \RuntimeException('Project directory must be a string.');
                    }
                    $lettreFile->move(
                        $projectDir . '/public/uploads/cancelatures',
                        $newFilename
                    );
                    $demande->setLettreMotivation($newFilename);
                } catch (\Symfony\Component\HttpFoundation\File\Exception\FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du fichier.');
                    return $this->render('bourse/apply.html.twig', [
                        'bourse' => $bourse,
                        'form' => $form->createView(),
                    ]);
                }
            } else {
                $this->addFlash('error', 'La lettre de motivation est obligatoire.');
                return $this->render('bourse/apply.html.twig', [
                    'bourse' => $bourse,
                    'form' => $form->createView(),
                ]);
            }

            $entityManager->persist($demande);
            $entityManager->flush();

            $this->addFlash('success', 'Votre candidature a été envoyée avec succès.');
            return $this->redirectToRoute('app_bourse_details_student', ['id' => $bourse->getId()]);
        }

        // If form has errors, display them
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
        }

        return $this->render('bourse/apply.html.twig', [
            'bourse' => $bourse,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/mes-candidatures', name: 'app_mes_candidatures', methods: ['GET'])]
    public function mesCandidatures(\Doctrine\ORM\EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_login');
        }

        $demandes = $entityManager->getRepository(\App\Entity\DemandeBourse::class)->findBy(
            ['etudiant' => $user],
            ['dateDemande' => 'DESC']
        );

        return $this->render('bourse/mes_candidatures.html.twig', [
            'demandes' => $demandes,
        ]);
    }

    #[Route('/candidature/{id}/annuler', name: 'app_candidature_annuler', methods: ['POST'])]
    public function annulerCandidature(
        \Symfony\Component\HttpFoundation\Request $request,
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        int $id
    ): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $demande = $entityManager->getRepository(\App\Entity\DemandeBourse::class)->find($id);

        if (!$demande || $demande->getEtudiant() !== $user) {
            $this->addFlash('error', 'Demande introuvable.');
            return $this->redirectToRoute('app_mes_candidatures');
        }

        if ($this->isCsrfTokenValid('annuler' . $demande->getId(), (string)$request->request->get('_token'))) {
            $entityManager->remove($demande);
            $entityManager->flush();
            $this->addFlash('success', 'Votre candidature a été annulée avec succès.');
        }

        return $this->redirectToRoute('app_mes_candidatures');
    }
}
