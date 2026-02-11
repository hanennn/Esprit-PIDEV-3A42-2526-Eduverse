<?php
namespace App\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Quiz;
use App\Form\QuizType;
use App\Entity\Certification;
use App\Entity\User;
use App\Entity\Cours;

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
        // 🔹 Valeurs de recherche
        $title  = $request->query->get('title', '');
        $type   = $request->query->get('type', '');
        $cours = $request->query->get('course', '');

        // 🔹 Valeurs de tri
        $sortTitle = $request->query->get('sort_title', '');
        $sortScore = $request->query->get('sort_score', '');
        $sortType  = $request->query->get('sort_type', '');

        // 🔹 Action cliquée (search ou sort)
        $action = $request->query->get('action', '');

        // QueryBuilder
        $qb = $quizRepository->createQueryBuilder('q')
            ->join('q.coursAssocie', 'c')
            ->addSelect('c');

        /* =========================
           🔍 RECHERCHE MULTICRITÈRE
        ==========================*/
        if ($action === 'search') {

            if (!empty($title)) {
                $qb->andWhere('q.titre LIKE :title')
                   ->setParameter('title', '%'.$title.'%');
            }

            if (!empty($type)) {
                $qb->andWhere('q.typeQuiz = :type')
                   ->setParameter('type', $type);
            }

            if (!empty($cours)) {
                $qb->andWhere('c.titre_cours LIKE :course')
                   ->setParameter('course', '%'.$cours.'%');
            }
        }

        /* =========================
           🔃 TRI MULTICRITÈRE
        ==========================*/
        if ($action === 'sort') {

            if (!empty($sortType)) {
                $qb->orderBy('q.typeQuiz', $sortType);
            }

            if (!empty($sortScore)) {
                $qb->addOrderBy('q.scoreMinimum', $sortScore);
            }

            if (!empty($sortTitle)) {
                $qb->addOrderBy('q.titre', $sortTitle);
            }
        }

        $quizs = $qb->getQuery()->getResult();

       // ================= STATISTIQUES =================
    $totalQuizzes = count($quizs);
    $totalCertifications = 0;
    $ranking = [];

    foreach ($quizs as $quiz) {
        $certCount = count($quiz->getCertifications());
        $totalCertifications += $certCount;

        $ranking[] = [
            'titre' => $quiz->getTitre(),
            'certCount' => $certCount
        ];
    }

    // Trier ranking : quiz avec le moins de certif = plus difficile
    usort($ranking, function($a, $b) {
        return $a['certCount'] <=> $b['certCount'];
    });

    return $this->render('back/quiz/index.html.twig', [
        'quizs' => $quizs,
        'filters' => [
            'title'      => $title,
            'type'       => $type,
            'course'     => $cours,
            'sort_title' => $sortTitle,
            'sort_score' => $sortScore,
            'sort_type'  => $sortType,
        ],
        'stats' => [
            'totalQuizzes' => $totalQuizzes,
            'totalCertifications' => $totalCertifications,
        ],
        'ranking' => $ranking
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
        $em->flush(); // Les modifications sont persistées automatiquement
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

// ==================== QUIZ TAKING ====================
    
    #[Route('/quiz/{id}/take', name: 'quiz_take', methods: ['GET', 'POST'])]
    public function takeQuiz(Request $request, Quiz $quiz, EntityManagerInterface $em): Response
    {
        // Get the currently logged-in user
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour passer un quiz.');
            return $this->redirectToRoute('app_login');
        }

        // Logique pour le quiz (calcul du score et enregistrement de la tentative)
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

            // Show result modal
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
}