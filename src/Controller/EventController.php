<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventInscription;
use App\Form\EventInscriptionType;
use App\Repository\EventRepository;
use App\Repository\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/event')]
class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_list', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        // Fetch all events, ordered by date
        $events = $eventRepository->createQueryBuilder('e')
            ->orderBy('e.date', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/mes-inscriptions', name: 'app_mes_inscriptions', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function mesInscriptions(EventInscriptionRepository $inscriptionRepo): Response
    {
        $inscriptions = $inscriptionRepo->findBy(
            ['participant' => $this->getUser()],
            ['dateInscription' => 'DESC']
        );

        return $this->render('event/mes_inscriptions.html.twig', [
            'inscriptions' => $inscriptions,
        ]);
    }

    #[Route('/{id}', name: 'app_event_details', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Event $event, EventInscriptionRepository $inscriptionRepo): Response
    {
        $isRegistered = false;
        if ($this->getUser()) {
            $isRegistered = (bool) $inscriptionRepo->findOneBy([
                'participant' => $this->getUser(),
                'event' => $event,
            ]);
        }

        return $this->render('event/show_public.html.twig', [
            'event' => $event,
            'isRegistered' => $isRegistered,
        ]);
    }

    #[Route('/{id}/register', name: 'app_event_register', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function register(Request $request, Event $event, EntityManagerInterface $entityManager, EventInscriptionRepository $inscriptionRepo): Response
    {
        $user = $this->getUser();

        // Check if already registered
        $existing = $inscriptionRepo->findOneBy([
            'participant' => $user,
            'event' => $event,
        ]);

        if ($existing) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');
            return $this->redirectToRoute('app_event_details', ['id' => $event->getId()]);
        }

        $inscription = new EventInscription();
        $inscription->setEvent($event);
        $inscription->setParticipant($user);
        $inscription->setDateInscription(new \DateTime());
        $inscription->setStatut('En attente');

        $form = $this->createForm(EventInscriptionType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($inscription);
            $entityManager->flush();

            $this->addFlash('success', 'Votre inscription a été enregistrée avec succès. Elle est en attente de validation.');

            return $this->redirectToRoute('app_mes_inscriptions');
        }

        return $this->render('event/register.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/inscription/{id}/annuler', name: 'app_inscription_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Request $request, EventInscription $inscription, EntityManagerInterface $entityManager): Response
    {
        if ($inscription->getParticipant() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas annuler cette inscription.');
        }

        if ($this->isCsrfTokenValid('cancel'.$inscription->getId(), $request->request->get('_token'))) {
            $entityManager->remove($inscription);
            $entityManager->flush();
            $this->addFlash('success', 'Votre inscription a été annulée.');
        }

        return $this->redirectToRoute('app_mes_inscriptions');
    }
}
