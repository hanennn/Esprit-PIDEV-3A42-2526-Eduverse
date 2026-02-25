<?php

namespace App\Controller;

use App\Entity\Bourse;
use App\Repository\BourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/bourseadmin')]
class BourseAdminController extends AbstractController
{
    #[Route('/', name: 'app_bourse_index', methods: ['GET'])]
    public function index(BourseRepository $bourseRepository): Response
    {
        $bourses = $bourseRepository->findAll();

        return $this->render('bourse_admin/index.html.twig', [
            'bourses' => $bourses,
        ]);
    }

    #[Route('/addbourse', name: 'app_bourse_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager, \Symfony\Component\Validator\Validator\ValidatorInterface $validator): Response
    {
        $oldValues = [];
        $errors = [];

        if ($request->isMethod('POST')) {
            $bourse = new Bourse();
            $titre = $request->request->get('titre');
            $description = $request->request->get('description');
            $montant = $request->request->get('montant');
            $dateDebStr = $request->request->get('dateAttribution');
            $dateFinStr = $request->request->get('dateFin');
            
            // Keep old values
            $oldValues = [
                'titre' => $titre,
                'description' => $description,
                'montant' => $montant,
                'dateAttribution' => $dateDebStr,
                'dateFin' => $dateFinStr
            ];

            // Set simple fields
            $bourse->setTitre((string)$titre);
            $bourse->setDescription((string)$description);
            // Handle float conversion safely
            $bourse->setMontant(empty($montant) ? 0.0 : (float)$montant);

            // Handle Dates
            if ($dateDebStr) {
                try {
                    $bourse->setDateAttribution(new \DateTime($dateDebStr));
                } catch (\Exception $e) { }
            }
            if ($dateFinStr) {
                try {
                    $bourse->setDateFin(new \DateTime($dateFinStr));
                } catch (\Exception $e) { }
            }
            
            // Validate
            $violationList = $validator->validate($bourse);
            if (count($violationList) > 0) {
                foreach ($violationList as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
            } else {
                // Handle Image Upload only if valid
                $imageFile = $request->files->get('image');
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = preg_replace('/[^a-zA-Z0-9]/', '', $originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                    try {
                        $imageFile->move(
                            $this->getParameter('kernel.project_dir').'/public/uploads/bourses',
                            $newFilename
                        );
                        $bourse->setImage('/uploads/bourses/'.$newFilename);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l\'image');
                    }
                }
                
                $entityManager->persist($bourse);
                $entityManager->flush();

                return $this->redirectToRoute('app_bourse_index');
            }
        }

        return $this->render('bourse_admin/add.html.twig', [
            'errors' => $errors,
            'old_values' => $oldValues
        ]);
    }

    #[Route('/{id}', name: 'app_bourse_show', methods: ['GET'])]
    public function show(Bourse $bourse): Response
    {
        return $this->render('bourse_admin/show.html.twig', [
            'bourse' => $bourse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_bourse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Bourse $bourse, EntityManagerInterface $entityManager, \Symfony\Component\Validator\Validator\ValidatorInterface $validator): Response
    {
        $errors = [];
        
        if ($request->isMethod('POST')) {
            $titre = $request->request->get('titre');
            $description = $request->request->get('description');
            $montant = $request->request->get('montant');
            $dateDebStr = $request->request->get('dateAttribution');
            $dateFinStr = $request->request->get('dateFin');

            $bourse->setTitre((string)$titre);
            $bourse->setDescription((string)$description);
            $bourse->setMontant(empty($montant) ? 0.0 : (float)$montant);

            if ($dateDebStr) {
                try { $bourse->setDateAttribution(new \DateTime($dateDebStr)); } catch (\Exception $e) { }
            }
            if ($dateFinStr) {
                try { $bourse->setDateFin(new \DateTime($dateFinStr)); } catch (\Exception $e) { }
            }

            // Validate
            $violationList = $validator->validate($bourse);
            if (count($violationList) > 0) {
                foreach ($violationList as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
            } else {
                $imageFile = $request->files->get('image');
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = preg_replace('/[^a-zA-Z0-9]/', '', $originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                    try {
                        $imageFile->move(
                            $this->getParameter('kernel.project_dir').'/public/uploads/bourses',
                            $newFilename
                        );
                        $bourse->setImage('/uploads/bourses/'.$newFilename);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l\'image');
                    }
                }

                $entityManager->flush();
                return $this->redirectToRoute('app_bourse_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('bourse_admin/edit.html.twig', [
            'bourse' => $bourse,
            'errors' => $errors
        ]);
    }

    #[Route('/{id}', name: 'app_bourse_delete', methods: ['POST'])]
    public function delete(Request $request, Bourse $bourse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$bourse->getId(), $request->request->get('_token'))) {
            $entityManager->remove($bourse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_bourse_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/applications', name: 'app_bourse_applications', methods: ['GET'])]
    public function viewApplications(Bourse $bourse): Response
    {
        $demandes = $bourse->getDemandes();
        
        return $this->render('bourse_admin/applications.html.twig', [
            'bourse' => $bourse,
            'demandes' => $demandes,
        ]);
    }

    #[Route('/demande/{id}/show', name: 'app_demande_show', methods: ['GET'])]
    public function showDemande(EntityManagerInterface $entityManager, int $id): Response
    {
        $demande = $entityManager->getRepository(\App\Entity\DemandeBourse::class)->find($id);
        if (!$demande) {
            throw $this->createNotFoundException('Demande non trouvée');
        }

        return $this->render('bourse_admin/show_demande.html.twig', [
            'demande' => $demande,
            'bourse' => $demande->getBourse(),
        ]);
    }

    #[Route('/demande/{id}/edit', name: 'app_demande_edit', methods: ['GET', 'POST'])]
    public function editDemande(Request $request, EntityManagerInterface $entityManager, int $id, \Symfony\Component\Mailer\MailerInterface $mailer): Response
    {
        $demande = $entityManager->getRepository(\App\Entity\DemandeBourse::class)->find($id);
        if (!$demande) {
            throw $this->createNotFoundException('Demande non trouvée');
        }

        $errors = [];

        if ($request->isMethod('POST')) {
            $statut = $request->request->get('statut');
            $niveauEtudes = $request->request->get('niveauEtudes');
            $note = $request->request->get('note');

            $oldStatut = $demande->getStatut();

            if (!$statut || !in_array($statut, ['En attente', 'Accepté', 'Refusé'])) {
                $errors['statut'] = 'Le statut est invalide.';
            }
            if (!$niveauEtudes) {
                $errors['niveauEtudes'] = "Le niveau d'études est obligatoire.";
            }

            if (empty($errors)) {
                $demande->setStatut($statut);
                $demande->setNiveauEtudes($niveauEtudes);
                $demande->setNote($note);
                $entityManager->flush();

                // Send email notification if status changed and is not 'En attente'
                if ($oldStatut !== $statut && $statut !== 'En attente') {
                    $etudiant = $demande->getEtudiant();
                    $bourse = $demande->getBourse();

                    if ($etudiant && $etudiant->getEmail()) {
                        $email = (new \Symfony\Bridge\Twig\Mime\TemplatedEmail())
                            ->from(new \Symfony\Component\Mime\Address('eduversee2026@gmail.com', 'Eduverse Administration'))
                            ->to($etudiant->getEmail())
                            ->subject('Mise à jour de votre demande de bourse')
                            ->htmlTemplate('emails/bourse/status_notification.html.twig')
                            ->context([
                                'etudiant' => $etudiant,
                                'bourse' => $bourse,
                                'statut' => $statut,
                                'note' => $note,
                            ]);

                        try {
                            $mailer->send($email);
                            $this->addFlash('success', 'Le statut a été mis à jour et un e-mail a été envoyé à l\'étudiant.');
                        } catch (\Exception $e) {
                            $this->addFlash('warning', 'Le statut a été mis à jour, mais l\'envoi de l\'e-mail a échoué.');
                            // Log the error for the admin
                            error_log('Mailer Error: ' . $e->getMessage());
                        }
                    }
                } else {
                    $this->addFlash('success', 'Le statut a été mis à jour.');
                }

                return $this->redirectToRoute('app_bourse_applications', ['id' => $demande->getBourse()->getId()]);
            }
        }

        return $this->render('bourse_admin/edit_demande.html.twig', [
            'demande' => $demande,
            'bourse' => $demande->getBourse(),
            'errors' => $errors,
        ]);
    }

    #[Route('/demande/{id}/delete', name: 'app_demande_delete', methods: ['POST'])]
    public function deleteDemande(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $demande = $entityManager->getRepository(\App\Entity\DemandeBourse::class)->find($id);
        if (!$demande) {
            throw $this->createNotFoundException('Demande non trouvée');
        }

        $bourseId = $demande->getBourse()->getId();

        if ($this->isCsrfTokenValid('delete_demande'.$demande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($demande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_bourse_applications', ['id' => $bourseId]);
    }
}
