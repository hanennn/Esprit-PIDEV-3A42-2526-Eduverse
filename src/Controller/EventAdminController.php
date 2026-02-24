<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/eventAdmin')]
class EventAdminController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->render('event_admin/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/addevent', name: 'app_event_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager, \Symfony\Component\Validator\Validator\ValidatorInterface $validator): Response
    {
        $oldValues = [];
        $errors = [];

        if ($request->isMethod('POST')) {
            $event = new Event();
            $titre = $request->request->get('titre');
            $description = $request->request->get('description');
            $type = $request->request->get('type');
            $lienWebinaire = $request->request->get('lienWebinaire');
            $niveau = $request->request->get('niveau');
            $dateStr = $request->request->get('date');
            $heureDebStr = $request->request->get('heureDeb');
            $heureFinStr = $request->request->get('heureFin');

            // Keep old values
            $oldValues = [
                'titre' => $titre,
                'description' => $description,
                'type' => $type,
                'lienWebinaire' => $lienWebinaire,
                'niveau' => $niveau,
                'date' => $dateStr,
                'heureDeb' => $heureDebStr,
                'heureFin' => $heureFinStr,
            ];

            // Set fields
            $event->setTitre((string)$titre);
            $event->setDescription((string)$description);
            $event->setType((string)$type);

            // Conditional fields
            if ($type === 'webinaire' && $lienWebinaire) {
                $event->setLienWebinaire($lienWebinaire);
            }
            if ($type === 'challenge' && $niveau) {
                $event->setNiveau($niveau);
            }

            // Handle Date
            if ($dateStr) {
                try {
                    $event->setDate(new \DateTime($dateStr));
                } catch (\Exception $e) { }
            }
            if ($heureDebStr) {
                try {
                    $event->setHeureDeb(new \DateTime($heureDebStr));
                } catch (\Exception $e) { }
            }
            if ($heureFinStr) {
                try {
                    $event->setHeureFin(new \DateTime($heureFinStr));
                } catch (\Exception $e) { }
            }

            // Validate
            $violationList = $validator->validate($event);
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
                            $this->getParameter('kernel.project_dir').'/public/uploads/events',
                            $newFilename
                        );
                        $event->setImage('/uploads/events/'.$newFilename);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l\'image');
                    }
                }

                $entityManager->persist($event);
                $entityManager->flush();

                return $this->redirectToRoute('app_event_index');
            }
        }

        return $this->render('event_admin/add.html.twig', [
            'errors' => $errors,
            'old_values' => $oldValues
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event_admin/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager, \Symfony\Component\Validator\Validator\ValidatorInterface $validator): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            $titre = $request->request->get('titre');
            $description = $request->request->get('description');
            $type = $request->request->get('type');
            $lienWebinaire = $request->request->get('lienWebinaire');
            $niveau = $request->request->get('niveau');
            $dateStr = $request->request->get('date');
            $heureDebStr = $request->request->get('heureDeb');
            $heureFinStr = $request->request->get('heureFin');

            $event->setTitre((string)$titre);
            $event->setDescription((string)$description);
            $event->setType((string)$type);

            // Reset conditional fields
            $event->setLienWebinaire(null);
            $event->setNiveau(null);

            if ($type === 'webinaire' && $lienWebinaire) {
                $event->setLienWebinaire($lienWebinaire);
            }
            if ($type === 'challenge' && $niveau) {
                $event->setNiveau($niveau);
            }

            if ($dateStr) {
                try { $event->setDate(new \DateTime($dateStr)); } catch (\Exception $e) { }
            }
            if ($heureDebStr) {
                try { $event->setHeureDeb(new \DateTime($heureDebStr)); } catch (\Exception $e) { }
            }
            if ($heureFinStr) {
                try { $event->setHeureFin(new \DateTime($heureFinStr)); } catch (\Exception $e) { }
            }

            // Validate
            $violationList = $validator->validate($event);
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
                            $this->getParameter('kernel.project_dir').'/public/uploads/events',
                            $newFilename
                        );
                        $event->setImage('/uploads/events/'.$newFilename);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l\'image');
                    }
                }

                $entityManager->flush();
                return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('event_admin/edit.html.twig', [
            'event' => $event,
            'errors' => $errors
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/inscriptions', name: 'app_event_inscriptions', methods: ['GET'])]
    public function viewInscriptions(Event $event): Response
    {
        $inscriptions = $event->getInscriptions();

        return $this->render('event_admin/inscriptions.html.twig', [
            'event' => $event,
            'inscriptions' => $inscriptions,
        ]);
    }

    #[Route('/inscription/{id}/show', name: 'app_event_inscription_show', methods: ['GET'])]
    public function showInscription(EntityManagerInterface $entityManager, int $id): Response
    {
        $inscription = $entityManager->getRepository(\App\Entity\EventInscription::class)->find($id);
        if (!$inscription) {
            throw $this->createNotFoundException('Inscription non trouvée');
        }

        return $this->render('event_admin/show_inscription.html.twig', [
            'inscription' => $inscription,
            'event' => $inscription->getEvent(),
        ]);
    }

    #[Route('/inscription/{id}/edit', name: 'app_event_inscription_edit', methods: ['GET', 'POST'])]
    public function editInscription(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $inscription = $entityManager->getRepository(\App\Entity\EventInscription::class)->find($id);
        if (!$inscription) {
            throw $this->createNotFoundException('Inscription non trouvée');
        }

        $errors = [];

        if ($request->isMethod('POST')) {
            $statut = $request->request->get('statut');
            $note = $request->request->get('note');

            if (!$statut || !in_array($statut, ['En attente', 'Accepté', 'Refusé'])) {
                $errors['statut'] = 'Le statut est invalide.';
            }

            if (empty($errors)) {
                $inscription->setStatut($statut);
                $inscription->setNote($note);
                $entityManager->flush();

                return $this->redirectToRoute('app_event_inscriptions', ['id' => $inscription->getEvent()->getId()]);
            }
        }

        return $this->render('event_admin/edit_inscription.html.twig', [
            'inscription' => $inscription,
            'event' => $inscription->getEvent(),
            'errors' => $errors,
        ]);
    }

    #[Route('/inscription/{id}/delete', name: 'app_event_inscription_delete', methods: ['POST'])]
    public function deleteInscription(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $inscription = $entityManager->getRepository(\App\Entity\EventInscription::class)->find($id);
        if (!$inscription) {
            throw $this->createNotFoundException('Inscription non trouvée');
        }

        $eventId = $inscription->getEvent()->getId();

        if ($this->isCsrfTokenValid('delete_inscription'.$inscription->getId(), $request->request->get('_token'))) {
            $entityManager->remove($inscription);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_inscriptions', ['id' => $eventId]);
    }
}
