<?php
namespace App\Controller\Back;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CertificationRepository;
use App\Entity\Quiz;
use App\Entity\Certification;
use App\Entity\User;
use App\Form\QuizType;
use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back/quiz')]
class QuizController extends AbstractController
{
  private $em;

    // Injecter EntityManagerInterface dans le constructeur
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em; // Sauvegarder l'EntityManager dans une variable de classe
    }

    // Route pour afficher la liste des quiz
    #[Route('/', name: 'back_quiz_index')]
    public function index(QuizRepository $quizRepository, Request $request): Response
    {
        $title  = $request->query->get('title', '');
        $type   = $request->query->get('type', '');
        $course = $request->query->get('course', '');
        $sortTitle = $request->query->get('sort_title', '');
        $sortScore = $request->query->get('sort_score', '');
        $sortType  = $request->query->get('sort_type', '');
        $action = $request->query->get('action', '');

        $qb = $quizRepository->createQueryBuilder('q')
            ->join('q.coursAssocie', 'c')
            ->addSelect('c');

        if ($action === 'search') {
            if (!empty($title)) $qb->andWhere('q.titre LIKE :title')->setParameter('title', '%'.$title.'%');
            if (!empty($type)) $qb->andWhere('q.typeQuiz = :type')->setParameter('type', $type);
            if (!empty($course)) $qb->andWhere('c.titre LIKE :course')->setParameter('course', '%'.$course.'%');
        }

        if ($action === 'sort') {
            if (!empty($sortType)) $qb->orderBy('q.typeQuiz', $sortType);
            if (!empty($sortScore)) $qb->addOrderBy('q.scoreMinimum', $sortScore);
            if (!empty($sortTitle)) $qb->addOrderBy('q.titre', $sortTitle);
        }

       $quizs = $qb->getQuery()->getResult();

// =================== STATS ===================
$totalQuizzes = count($quizs);

$totalAttempts = 0;   // toutes les tentatives
$totalSuccess  = 0;   // tentatives réussies

$rankings = []; // pour classement easy/hard

foreach ($quizs as $quiz) {
    $attempts = $quiz->getCertifications(); // tes tentatives
    $attemptCount = count($attempts);

    $successCount = 0;
    foreach ($attempts as $a) {
        if ($a->getStatut() === 'Réussi') {
            $successCount++;
        }
    }

    $totalAttempts += $attemptCount;
    $totalSuccess  += $successCount;

    $rate = $attemptCount > 0 ? round(($successCount / $attemptCount) * 100, 1) : 0;

    $rankings[] = [
        'id'       => $quiz->getId(),
        'titre'    => $quiz->getTitre(),
        'attempts' => $attemptCount,
        'success'  => $successCount,
        'rate'     => $rate,
    ];
}

// Taux global
$globalRate = $totalAttempts > 0 ? round(($totalSuccess / $totalAttempts) * 100, 1) : 0;

// EASY -> HARD : plus de réussites = plus easy
usort($rankings, function ($a, $b) {
    if ($a['success'] !== $b['success']) return $b['success'] <=> $a['success'];
    if ($a['rate'] !== $b['rate']) return $b['rate'] <=> $a['rate'];
    return $b['attempts'] <=> $a['attempts'];
});

$topEasy = array_slice($rankings, 0, 5);
$topHard = array_slice(array_reverse($rankings), 0, 5);

return $this->render('back/quiz/index.html.twig', [
    'quizs' => $quizs,
    'filters' => [
        'title'      => $title,
        'type'       => $type,
        'course'     => $course,
        'sort_title' => $sortTitle,
        'sort_score' => $sortScore,
        'sort_type'  => $sortType,
    ],
    'stats' => [
        'totalQuizzes'  => $totalQuizzes,
        'totalAttempts' => $totalAttempts,
        'totalSuccess'  => $totalSuccess,
        'globalRate'    => $globalRate,
    ],
    'topEasy' => $topEasy,
    'topHard' => $topHard,
]);
    }

       // 🔹 Créer un quiz (route utilisable Front et Back)
    #[Route('/new', name: 'back_quiz_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $quiz = new Quiz();
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($quiz);
            $em->flush();
            $this->addFlash('success', 'Quiz créé avec succès !');

            // Redirection vers l'ajout de questions
            return $this->redirectToRoute('back_quiz_add_questions', [
                'quizId' => $quiz->getId()
            ]);
        }

        return $this->render('front/quiz/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    // 🔹 Modifier un quiz
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

        return $this->render('front/quiz/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    // 🔹 Supprimer un quiz
    #[Route('/delete/{id}', name: 'back_quiz_delete', methods:['POST'])]
    public function deleteQuiz(Request $request, Quiz $quiz, EntityManagerInterface $em)
    {
        if ($this->isCsrfTokenValid('delete'.$quiz->getId(), $request->request->get('_token'))) {
            $em->remove($quiz);
            $em->flush();
            $this->addFlash('success', 'Quiz supprimé avec succès !');
        }
        return $this->redirectToRoute('front_quiz_list');
    }
 #[Route('/back/quiz/quiz/{id}/take', name: 'front_quiz_take', methods: ['GET', 'POST'])]
public function takeQuiz(Request $request, Quiz $quiz, EntityManagerInterface $em): Response
{
    $user = $em->getRepository(User::class)->find(1); // Utilisateur avec ID 1
    if (!$user) {
        throw $this->createNotFoundException("Utilisateur introuvable.");
    }

    // Logique pour le quiz (calcul du score et enregistrement de la tentative)
    $questions = $quiz->getQuestions();
    $totalPoints = 0;
    foreach ($questions as $q) {
        $totalPoints += (int) $q->getPoints();
    }

    $score = 0;
    $showResult = false; // Initialize this variable

    if ($request->isMethod('POST')) {
        foreach ($questions as $question) {
            $userAnswer = $request->request->get('question_' . $question->getId());
            if ($userAnswer === null) {
                continue;
            }

            // Logique pour vérifier la réponse et calculer le score
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

        // Sauvegarder la tentative
        $half = $totalPoints / 2;
        $status = ($score >= $half) ? 'Réussi' : 'Échoué';
        $badge = ($score >= $half) ? 'Or' : 'Bronze';

        // Créer et persister la tentative
        $attempt = new Certification();
        $attempt->setQuiz($quiz);
        $attempt->setUser($user);
        $attempt->setDateAttribution(new \DateTime());
        $attempt->setScoreObtenu($score);
        $attempt->setStatut($status);
        $attempt->setBadge($badge);

        $em->persist($attempt);
        $em->flush();

        // Show result modal
        $showResult = true;
    }

    return $this->render('front/quiz/take.html.twig', [
        'quiz' => $quiz,
        'questions' => $questions,
        'score' => $score,
        'totalPoints' => $totalPoints,
        'showResult' => $showResult,  // Pass showResult to the template
    ]);
}
    // Route pour afficher les tentatives de chaque quiz
   #[Route('/{id}/attempts', name: 'back_quiz_attempts')]
public function attempts(Quiz $quiz): Response
{
    // Récupérer les tentatives pour ce quiz
    $attempts = $this->em->getRepository(Certification::class)->findBy(
        ['quiz' => $quiz],
        ['dateAttribution' => 'DESC'] // Trier les tentatives par date d'attribution (du plus récent au plus ancien)
    );

    return $this->render('back/quiz/attempts.html.twig', [
        'quiz' => $quiz,
        'attempts' => $attempts
    ]);
}
}
