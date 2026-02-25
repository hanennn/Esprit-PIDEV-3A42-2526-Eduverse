<?php
namespace App\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Quiz;
use App\Form\QuizType;
use App\Entity\Certification;
use App\Entity\User;
use App\Entity\Cours;
use App\Form\QuizStartType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/back/quiz')]
class QuizController extends AbstractController
{
    #[Route('/', name: 'back_quiz_index')]
public function index(QuizRepository $quizRepository, Request $request): Response
{
    //  Valeurs de recherche
    $title  = $request->query->get('title', '');
    $type   = $request->query->get('type', '');
    $cours  = $request->query->get('course', '');

    //  Valeurs de tri
    $sortTitle = $request->query->get('sort_title', '');
    $sortScore = $request->query->get('sort_score', '');
    $sortType  = $request->query->get('sort_type', '');

    //  QueryBuilder
    $qb = $quizRepository->createQueryBuilder('q')
        ->leftJoin('q.coursAssocie', 'c')
        ->addSelect('c');


    if ($title !== '') {
        $qb->andWhere('q.titre LIKE :title')
           ->setParameter('title', '%' . $title . '%');
    }

    if ($type !== '') {
        $qb->andWhere('q.typeQuiz = :type')
           ->setParameter('type', $type);
    }

    if ($cours !== '') {
        $qb->andWhere('c.titre_cours LIKE :course')
           ->setParameter('course', '%' . $cours . '%');
    }

    // TRI
    $hasOrder = false;

    if ($sortType !== '') {
        $qb->orderBy('q.typeQuiz', $sortType);
        $hasOrder = true;
    }

    if ($sortScore !== '') {
        $hasOrder ? $qb->addOrderBy('q.scoreMinimum', $sortScore)
                  : $qb->orderBy('q.scoreMinimum', $sortScore);
        $hasOrder = true;
    }

    if ($sortTitle !== '') {
        $hasOrder ? $qb->addOrderBy('q.titre', $sortTitle)
                  : $qb->orderBy('q.titre', $sortTitle);
        $hasOrder = true;
    }

    if (!$hasOrder) {
        $qb->orderBy('q.id', 'DESC');
    }

    $quizs = $qb->getQuery()->getResult();

   
    $totalQuizzes = count($quizs);
    $totalCertifications = 0;
    $ranking = [];

    foreach ($quizs as $quiz) {
        $certCount = count($quiz->getCertifications());
        $totalCertifications += $certCount;

        $ranking[] = [
            'titre' => $quiz->getTitre(),
            'certCount' => $certCount,
        ];
    }

    usort($ranking, fn($a, $b) => $a['certCount'] <=> $b['certCount']);

    
    $filters = [
        'title'      => $title,
        'type'       => $type,
        'course'     => $cours,
        'sort_title' => $sortTitle,
        'sort_score' => $sortScore,
        'sort_type'  => $sortType,
    ];

   
    $defaultStats = [
        'totalUsers' => 0,
        'activeCourses' => 0,
        'newCourses' => 0,
        'totalInstructors' => 0,
        'newInstructors' => 0,
        'scholarshipRequests' => 0,
        'pendingScholarships' => 0,
        'totalStudents' => 0,
        'totalReviews' => 0,
        'averageRating' => 0,
        'averageSuccessRate' => 0,
    ];

    return $this->render('backoffice.html.twig', [
        // ✅ QUIZ
        'quizs' => $quizs,
        'filters' => $filters,
        'totalQuizzes' => $totalQuizzes,
        'totalCertifications' => $totalCertifications,
        'ranking' => $ranking,
        'averageScore' => 0,

        // ✅ DASHBOARD / USERS / COURSES / STATS 
        'stats' => $defaultStats,
        'recentActivities' => [],
        'popularCourses' => [],
        'users' => [],

        'searchQuery' => '',
        'statusFilter' => '',
        'currentFilter' => 'all',
        'userCounts' => ['total' => 0, 'active' => 0, 'inactive' => 0, 'recent' => 0],

        'allCourses' => [],
        'totalCourses' => 0,
        'totalLanguages' => 0,
        'totalSubjects' => 0,
        'courseFilters' => ['search' => '', 'criteria' => 'titre', 'sort' => 'id'],

        'fullStats' => [
            'bourses' => [
                'total' => 0, 'demandes' => 0, 'totalAmount' => 0,
                'statusDistribution' => [], 'chartData' => ['labels' => [], 'data' => []],
            ],
            'events' => [
                'total' => 0, 'inscriptions' => 0,
                'typeDistribution' => [], 'topEvents' => [],
                'chartData' => ['labels' => [], 'data' => []],
            ],
        ],

        'chartData' => ['labels' => [], 'datasets' => []],
    ]);
}

   #[Route('/new', name: 'new_quiz')]
public function new(Request $request, EntityManagerInterface $em): Response
{
    $quiz = new Quiz();
    $form = $this->createForm(QuizType::class, $quiz);

    $form->handleRequest($request);


    if ($form->isSubmitted() && !$form->isValid()) {
        dump($form->getErrors(true, false));
    }

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($quiz);
        $em->flush();

        $this->addFlash('success', 'Quiz créé avec succès !');

        return $this->redirectToRoute('back_quiz_add_questions', [
            'quizId' => $quiz->getId()
        ]);
    }

    return $this->render('NewQuiz.html.twig', [
        'form' => $form->createView(),
    ]);
}
    #[Route('/edit/{id}', name: 'back_quiz_edit')]
public function edit(Quiz $quiz, Request $request, EntityManagerInterface $em): Response
{
    $form = $this->createForm(QuizType::class, $quiz);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush(); 
        $this->addFlash('success', 'Quiz modifié avec succès !');
        return $this->redirectToRoute('back_quiz_index');
    }

    return $this->redirect($request->headers->get('referer'));
}
#[Route('/delete/{id}', name: 'back_quiz_delete', methods: ['POST'])]
public function deleteQuiz(Request $request, int $id, EntityManagerInterface $em, QuizRepository $quizRepository): Response
{
    $quiz = $quizRepository->find($id);
    
    if (!$quiz) {
        $this->addFlash('error', 'Quiz introuvable !');
        return $this->redirectToRoute('back_quiz_index');
    }
    
    if ($this->isCsrfTokenValid('delete'.$quiz->getId(), $request->request->get('_token'))) {
        $em->remove($quiz);
        $em->flush();
        $this->addFlash('success', 'Quiz supprimé avec succès !');
    } else {
        $this->addFlash('error', 'Token CSRF invalide !');
    }
    
    return $this->redirectToRoute('app_profile');
}


    
 #[Route('/quiz/{id}/take', name: 'quiz_take', methods: ['GET', 'POST'])]
    public function takeQuiz(Request $request, Quiz $quiz, EntityManagerInterface $em): Response
    {
        
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour passer un quiz.');
            return $this->redirectToRoute('app_login');
        }

        
        $questions = $quiz->getQuestions();
        $totalPoints = 0;
        foreach ($questions as $q) {
            $totalPoints += (int) $q->getPoints();
        }

        $score = 0;
        $showResult = false;

        if ($request->isMethod('POST')) {
            foreach ($questions as $question) {
                $userAnswer = $request->request->get('question_' . $question->getId());
                if ($userAnswer === null) {
                    continue;
                }

                
                $userAnswer = (int) $userAnswer;
                $reponses = $question->getReponses();
                if (!isset($reponses[$userAnswer])) {
                    continue;
                }
                $chosen = $reponses[$userAnswer];
                if (!empty($chosen['correct'])) {
                    $score += (int) $question->getPoints();
                }
            }

            
            $half = $totalPoints / 2;
            $status = ($score >= $half) ? 'Réussi' : 'Échoué';
            $badge = ($score >= $half) ? 'Or' : 'Bronze';

           
            $attempt = new \App\Entity\Certification();
            $attempt->setQuiz($quiz);
            $attempt->setUser($user);
            $attempt->setDateAttribution(new \DateTime());
            $attempt->setScoreObtenu($score);
            $attempt->setStatut($status);
            $attempt->setBadge($badge);

            $em->persist($attempt);
            $em->flush();

            $this->addFlash('success', "Quiz terminé! Score: $score / $totalPoints");

           
            $showResult = true;
        }

        return $this->render('TakeQuiz.html.twig', [
            'quiz' => $quiz,
            'questions' => $questions,
            'score' => $score,
            'totalPoints' => $totalPoints,
            'showResult' => $showResult,
        ]);
    }



    #[Route('/quiz/update/{id}', name: 'app_instructor_update_quiz', methods: ['POST'])]
public function updateQuiz(Request $request, int $id, EntityManagerInterface $em, QuizRepository $quizRepository): Response
{
    $quiz = $quizRepository->find($id);
    
    if (!$quiz) {
        $this->addFlash('error', 'Quiz introuvable !');
        return $this->redirectToRoute('app_profile');
    }
    
    
    $cours = $quiz->getCoursAssocie();
    if ($cours->getCreateur() !== $this->getUser()) {
        $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier ce quiz !');
        return $this->redirectToRoute('app_profile');
    }
    
    // Update quiz title
    if ($request->request->has('title')) {
        $quiz->setTitre($request->request->get('title'));
    }
    
    $em->flush();
    
    $this->addFlash('success', 'Quiz mis à jour avec succès !');
    return $this->redirectToRoute('app_profile');
}
#[Route('/update-admin/{id}', name: 'back_quiz_update_admin', methods: ['POST'])]
public function updateAdminQuiz(Request $request, Quiz $quiz, EntityManagerInterface $em): Response
{
    
    // $this->denyAccessUnlessGranted('ROLE_ADMIN');

   
    $titre = trim((string) $request->request->get('titre', ''));
    $typeQuiz = (string) $request->request->get('typeQuiz', '');
    $duree = (int) $request->request->get('duree', 0);
    $scoreMinimum = (float) $request->request->get('scoreMinimum', 0);

    
    if ($titre === '' || !in_array($typeQuiz, ['Intermédiaire', 'Final'], true) || $duree <= 0 || $scoreMinimum < 0) {
        $this->addFlash('error', 'Données invalides.');
        return $this->redirectToRoute('back_quiz_index');
    }

    
    $quiz->setTitre($titre);
    $quiz->setTypeQuiz($typeQuiz);
    $quiz->setDuree($duree);
    $quiz->setScoreMinimum($scoreMinimum);

    $em->flush();

    $this->addFlash('success', 'Quiz modifié avec succès !');
    return $this->redirectToRoute('back_quiz_index');
}
 #[Route('/quiz/{id}/react', name: 'quiz_react', methods: ['POST'])]
    public function react(Quiz $quiz, Request $request, SessionInterface $session): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour réagir.');
            return $this->redirectToRoute('app_login');
        }

        // Enregistrer l'action (like/dislike) dans la session
        $reaction = $request->get('reaction'); // "like" ou "dislike"
        $session->set('quiz_' . $quiz->getId() . '_reaction', $reaction);

        // Rediriger vers la page du quiz
        $this->addFlash('success', 'Votre réaction a été enregistrée.');
        return $this->redirectToRoute('app_profile', ['id' => $quiz->getId()]);
    }

}