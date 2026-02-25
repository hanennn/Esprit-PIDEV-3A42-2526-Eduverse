<?php

namespace App\Controller\Front;

use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/front/quiz')]
class QuizController extends AbstractController
{
    #[Route('/', name: 'front_quiz_list', methods: ['GET'])]
    public function list(QuizRepository $quizRepository, Request $request): Response
    {
        $title  = $request->query->get('title', '');
        $type   = $request->query->get('type', '');
        $course = $request->query->get('course', '');

        // Tri multi-critères (ASC / DESC)
        $sortTitle = $request->query->get('sort_title', '');
        $sortScore = $request->query->get('sort_score', '');
        $sortType  = $request->query->get('sort_type', '');

        $qb = $quizRepository->createQueryBuilder('q')
            ->leftJoin('q.coursAssocie', 'c')
            ->addSelect('c');

        // ✅ Filtres
        if ($title !== '') {
            $qb->andWhere('q.titre LIKE :title')
               ->setParameter('title', '%'.$title.'%');
        }

        if ($type !== '') {
            $qb->andWhere('q.typeQuiz = :type')
               ->setParameter('type', $type);
        }

        if ($course !== '') {
            $qb->andWhere('c.titre LIKE :course')
               ->setParameter('course', '%'.$course.'%');
        }

        // ✅ Tri (sécurisé)
        $allowed = ['ASC', 'DESC'];

        if (in_array($sortType, $allowed, true)) {
            $qb->addOrderBy('q.typeQuiz', $sortType);
        }
        if (in_array($sortScore, $allowed, true)) {
            $qb->addOrderBy('q.scoreMinimum', $sortScore);
        }
        if (in_array($sortTitle, $allowed, true)) {
            $qb->addOrderBy('q.titre', $sortTitle);
        }

        $quizs = $qb->getQuery()->getResult();

        return $this->render('front/quiz/listformateur.html.twig', [
            'quizs' => $quizs,
            'filters' => [
                'title' => $title,
                'type' => $type,
                'course' => $course,
                'sort_title' => $sortTitle,
                'sort_score' => $sortScore,
                'sort_type' => $sortType,
            ],
        ]);
    }
}
