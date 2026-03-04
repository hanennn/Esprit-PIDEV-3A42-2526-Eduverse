<?php

namespace App\Controller;

use App\Entity\User;

use App\Entity\Cours;
use App\Entity\Inscription;
use App\Entity\Quiz;
use App\Form\CoursType;
use App\Repository\CoursRepository;
use App\Repository\ChapitresRepository;
use App\Repository\InscriptionRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/front')]
class CoursController extends AbstractController
{
    // ==================== STUDENT/USER VIEWS ====================
    
    #[Route('/courses', name: 'courses')]
    public function index(CoursRepository $coursRepository, Request $request): Response
    {
        $search = $request->query->getString('search', '');
        $sort = $request->query->getString('sort', '');
        $criteria = $request->query->getString('criteria', 'titre');
        $favorites = $request->getSession()->get('favorites', []);

        $cours = $coursRepository->searchAndSort($search, $criteria, $sort);
        
        return $this->render('front/cours.html.twig', [
            'cours' => $cours,
            'favorites' => $favorites,
            'search' => $search,
            'sort' => $sort,
            'criteria' => $criteria,
        ]);
    }

    #[Route('/course/{id}', name: 'course_show')]
    public function show(
        Cours $cours, 
        ChapitresRepository $chapitresRepository, 
        InscriptionRepository $inscriptionRepository,
        EntityManagerInterface $em
    ): Response {
        $chapitres = $chapitresRepository->findBy(
            ['cours' => $cours->getId()],
            ['ordre_chap' => 'ASC']
        );

        // NEW: Get quizzes for this course
        $quizzes = $em->getRepository(Quiz::class)->findBy(
            ['coursAssocie' => $cours],
            ['typeQuiz' => 'ASC', 'titre' => 'ASC']
        );

        $user = $this->getUser();
        $isEnrolled = false;
        if ($user) {
            $existing = $inscriptionRepository->findOneBy(['cours' => $cours, 'user' => $user]);
            $isEnrolled = (bool) $existing;
        }

        return $this->render('Cours.html.twig', [
            'cours' => $cours,
            'chapitres' => $chapitres,
            'quizzes' => $quizzes,  // NEW: Pass quizzes to template
            'isEnrolled' => $isEnrolled,
        ]);
    }

    #[Route('/course/{id}/enroll', name: 'course_enroll', methods: ['POST'])]
    public function enroll(Cours $cours, Request $request, EntityManagerInterface $em, InscriptionRepository $inscriptionRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $token = $request->request->getString('_token');
        if (!$this->isCsrfTokenValid('enroll'.$cours->getId(), $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        // prevent duplicate
        $existing = $inscriptionRepository->findOneBy(['cours' => $cours, 'user' => $user]);
        if ($existing) {
            $this->addFlash('info', 'Vous êtes déjà inscrit à ce cours.');
            return $this->redirectToRoute('course_show', ['id' => $cours->getId()]);
        }

        $inscription = new Inscription();
        $inscription->setCours($cours);
        $inscription->setUser($user);
        $inscription->setCreatedAt(new \DateTimeImmutable());

        $em->persist($inscription);
        $em->flush();

        $this->addFlash('success', 'Inscription réussie.');

        return $this->redirectToRoute('course_show', ['id' => $cours->getId()]);
    }

    #[Route('/favorites/toggle/{id}', name: 'toggle_favorite', methods: ['POST'])]
    public function toggleFavorite(int $id, Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'message' => 'Requête invalide'], 400);
        }

        $session = $request->getSession();
        $favorites = $session->get('favorites', []);

        if (in_array($id, $favorites)) {
            $favorites = array_diff($favorites, [$id]);
            $added = false;
        } else {
            $favorites[] = $id;
            $added = true;
        }

        $session->set('favorites', $favorites);

        return new JsonResponse(['success' => true, 'added' => $added]);
    }

    // ==================== INSTRUCTOR MANAGEMENT ====================
    
    #[Route('/cours/manage', name: 'front_cours_index', methods: ['GET', 'POST'])]
    public function manageCours(
        Request $request,
        EntityManagerInterface $em,
        CoursRepository $coursRepo
    ): Response {
        $search = $request->query->getString('search');
        $searchCriteria = $request->query->getString('search_criteria', 'titre');
        $sort = $request->query->getString('sort', 'titre');

        $coursList = $coursRepo->searchAndSort($search, $searchCriteria, $sort);

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }
        // Formulaire ajout cours
        $cours = new Cours();
        $cours->setCreateur($user);
        $formCours = $this->createForm(CoursType::class, $cours);
        $formCours->handleRequest($request);

        if ($formCours->isSubmitted() && $formCours->isValid()) {
            $em->persist($cours);
            $em->flush();

            return $this->redirectToRoute('front_cours_index');
        }

        return $this->render('NewCours.html.twig', [
            'cours' => $coursList,
            'formCours' => $formCours->createView(),
            'search_criteria' => $searchCriteria,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    #[Route('/cours/{id}/edit', name: 'front_cours_edit', methods: ['GET', 'POST'])]
    public function editCours(
        Request $request,
        Cours $cours,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('front_cours_index');
        }

        return $this->render('front/cours/edit.html.twig', [
            'form' => $form->createView(),
            'cours' => $cours,
        ]);
    }

    #[Route('/cours/{id}/delete', name: 'front_cours_delete', methods: ['POST'])]
    public function deleteCours(
        Request $request,
        Cours $cours,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$cours->getId(), $request->request->getString('_token'))) {
            $em->remove($cours);
            $em->flush();
        }

        return $this->redirectToRoute('front_cours_index');
    }

    #[Route('/cours/catalogue', name: 'front_cours_catalogue', methods: ['GET'])]
    public function catalogue(
        Request $request,
        CoursRepository $coursRepo,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->getString('search');
        $searchCriteria = $request->query->getString('search_criteria', 'titre');
        $sort = $request->query->getString('sort', 'titre');

        $cours = $paginator->paginate(
        $coursRepo->searchAndSort($search, $searchCriteria, $sort),
        $request->query->getInt('page', 1),
        2
    );

        return $this->render('ListeCours.html.twig', [
            'cours' => $cours,
            'search_criteria' => $searchCriteria,
            'search' => $search,
            'sort' => $sort,
        ]);
    }
}
