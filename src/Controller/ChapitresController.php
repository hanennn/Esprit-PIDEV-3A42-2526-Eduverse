<?php

namespace App\Controller;
use App\Entity\Quiz;
use App\Entity\Cours;
use App\Entity\Chapitres;
use App\Form\CoursType;
use App\Form\ChapitresType;
use App\Repository\CoursRepository;
use App\Repository\ChapitresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;


#[Route('/front')]
class ChapitresController extends AbstractController
{
    // ==================== COURS MANAGEMENT ====================
    
    #[Route('/cours', name: 'front_cours_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        CoursRepository $coursRepo
    ): Response {
        $search = $request->query->get('search');
        $searchCriteria = $request->query->get('search_criteria', 'titre');
        $sort = $request->query->get('sort', 'titre');

        $coursList = $coursRepo->searchAndSort($search, $searchCriteria, $sort);

        // Add new cours form
        $cours = new Cours();
        $formCours = $this->createForm(CoursType::class, $cours);
        $formCours->handleRequest($request);

        if ($formCours->isSubmitted() && $formCours->isValid()) {
            $em->persist($cours);
            $em->flush();

            return $this->redirectToRoute('front_cours_index');
        }

        // Chapter forms for each cours
        $forms = [];
        foreach ($coursList as $c) {
            $forms[$c->getId()] = $this->createForm(ChapitresType::class)->createView();
        }

        return $this->render('front/cours/index.html.twig', [
            'cours' => $coursList,
            'formCours' => $formCours->createView(),
            'forms' => $forms,
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
            
        ]);
    }

    #[Route('/cours/{id}/delete', name: 'front_cours_delete', methods: ['POST'])]
    public function deleteCours(
        Request $request,
        Cours $cours,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$cours->getId(), $request->request->get('_token'))) {
            $em->remove($cours);
            $em->flush();
        }

        return $this->redirectToRoute('front_cours_index');
    }

    #[Route('/cours/{id}/chapitres', name: 'back_chapitre_show', methods: ['GET'])]
    public function listChapitreByCours(
        Cours $cours,
        ChapitresRepository $chapitreRepository
    ): Response {
        $chapitres = $chapitreRepository->findBy(['cours' => $cours]);

        return $this->render('ListChapitres.html.twig', [
            'chapitres' => $chapitres,
            'cours' => $cours,
        ]);
    }

    // ==================== CHAPITRE MANAGEMENT ====================
    
    #[Route('/cours/{id}/chapitre', name: 'front_chapitre_add', methods: ['GET', 'POST'])]
    public function addChapitre(
        Cours $cours,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $chapitre = new Chapitres();
        $chapitre->setCours($cours);

        $formChapitre = $this->createForm(ChapitresType::class, $chapitre);
        $formChapitre->handleRequest($request);

        if ($formChapitre->isSubmitted() && $formChapitre->isValid()) {
            $file = $formChapitre->get('contenuChap')->getData();
            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('uploads_directory'), $filename);
                $chapitre->setContenuChap($filename);
            }

            $em->persist($chapitre);
            $em->flush();

            return $this->redirectToRoute('front_cours_index');
        }

        return $this->render('NewChapitres.html.twig', [
            'cours' => $cours,
            'formChapitre' => $formChapitre->createView(),
        ]);
    }

    #[Route('/chapitre/{id}', name: 'chapitre_contenu', methods: ['GET'])]
    public function showChapitre(
        ChapitresRepository $chapitreRepository, 
        int $id, 
        EntityManagerInterface $em
    ): Response {
        $chapitre = $chapitreRepository->find($id);

        if (!$chapitre) {
            throw $this->createNotFoundException('Chapitre non trouvé');
        }
        
        // Auto-open chapter when viewed
        if ($chapitre->getStatutChap() !== 'Ouvert') {
            $chapitre->setStatutChap('Ouvert');
            $em->flush();
        }

        return $this->render('front/chapitre_show.html.twig', [
            'chapitre' => $chapitre,
        ]);
    }

    #[Route('/chapitre/{id}/edit', name: 'front_chapitre_edit', methods: ['GET', 'POST'])]
    public function editChapitre(
        Request $request,
        Chapitres $chapitre,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(ChapitresType::class, $chapitre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('contenuChap')->getData();
            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('uploads_directory'), $filename);
                $chapitre->setContenuChap($filename);
            }

            $em->flush();
            return $this->redirectToRoute('front_cours_index');
        }

        return $this->render('ListChapitres.html.twig', [
            'cours' => $chapitre->getCours(),
            'form' => $form->createView(),
            'chapitres' => $chapitre->getCours()->getChapitres(),
            'editId' => $chapitre->getId()
        ]);
    }

    #[Route('/chapitre/{id}/delete', name: 'front_chapitre_delete', methods: ['POST'])]
    public function deleteChapitre(
        Request $request,
        Chapitres $chapitre,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$chapitre->getId(), $request->request->get('_token'))) {
            $em->remove($chapitre);
            $em->flush();
        }

        return $this->redirectToRoute('front_cours_index');
    }


    // ==================== PUBLIC STUDENT VIEW ====================

    #[Route('/cours/catalogue', name: 'front_cours_catalogue', methods: ['GET'])]
    public function catalogue(
        Request $request,
        CoursRepository $coursRepo
    ): Response {
        // Get search and sort parameters
        $search = $request->query->get('search');
        $searchCriteria = $request->query->get('search_criteria', 'titre');
        $sort = $request->query->get('sort', 'titre');

        // Get filtered and sorted courses
        $coursList = $coursRepo->searchAndSort($search, $searchCriteria, $sort);

        return $this->render('ListeCours.html.twig', [
            'cours' => $coursList,
            'search_criteria' => $searchCriteria,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    #[Route('/cours/{id}/public', name: 'front_cours_public_chapitres', methods: ['GET'])]
public function publicChapitres(
    Cours $cours,
    ChapitresRepository $chapitreRepository,
    Request $request
): Response {

    // ✅ Chapitres triés (corrigé)
    $chapitres = $chapitreRepository->findBy(
        ['cours' => $cours],
        ['ordre_chap' => 'ASC']
    );

    // ✅ Filtres quiz
    $q = trim((string) $request->query->get('q', ''));
    $typeFilter = (string) $request->query->get('type', '');
    $sort = (string) $request->query->get('sort', 'recent');

    // ✅ quizzes du cours
    $quizzes = $cours->getQuizzes()->toArray();

    // filtre titre
    if ($q !== '') {
        $quizzes = array_filter($quizzes, fn(Quiz $quiz) =>
            stripos($quiz->getTitre(), $q) !== false
        );
    }

    // filtre type
    if ($typeFilter !== '' && in_array($typeFilter, ['Intermédiaire', 'Final'], true)) {
        $quizzes = array_filter($quizzes, fn(Quiz $quiz) =>
            $quiz->getTypeQuiz() === $typeFilter
        );
    }

    // tri
    usort($quizzes, function(Quiz $a, Quiz $b) use ($sort) {
        return match ($sort) {
            'title_asc'  => strcmp($a->getTitre(), $b->getTitre()),
            'title_desc' => strcmp($b->getTitre(), $a->getTitre()),
            'score_asc'  => ($a->getScoreMinimum() <=> $b->getScoreMinimum()),
            'score_desc' => ($b->getScoreMinimum() <=> $a->getScoreMinimum()),
            'duree_asc'  => ($a->getDuree() <=> $b->getDuree()),
            'duree_desc' => ($b->getDuree() <=> $a->getDuree()),
            default      => ($b->getId() <=> $a->getId()), // recent
        };
    });

    // ✅ envoyer filters au twig
    $quizFilters = ['q' => $q, 'type' => $typeFilter, 'sort' => $sort];

    return $this->render('Cours.html.twig', [
        'cours' => $cours,
        'chapitres' => $chapitres,
        'quizzes' => $quizzes,
        'quizFilters' => $quizFilters,

        // si tu utilises isEnrolled dans twig, envoie une valeur (temporaire)
        'isEnrolled' => false,
    ]);
}

#[Route('/chapitre/{id}/resume', name: 'chapitre_resume', methods: ['GET'])]
public function genererResume(Chapitres $chapitre,HttpClientInterface $client): Response {
    $cheminPdf = $this->getParameter('kernel.project_dir')
                 . '/public/uploads/'
                 . $chapitre->getContenuChap();

    $response = $client->request('POST', 'http://localhost:5001/resume', [
        'json' => ['chemin_pdf' => $cheminPdf],
        'timeout' => 120, // 👈 ajouté
    ]);

    $data = $response->toArray();
    

    return $this->render('front/chapitre_show.html.twig', [
        'chapitre' => $chapitre,
        'resume' => $data['resume'],
    ]);
}
}