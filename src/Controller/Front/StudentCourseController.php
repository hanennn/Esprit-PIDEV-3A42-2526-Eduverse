<?php
namespace App\Controller\Front;

use App\Entity\Course;
use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\QuizRepository;

#[Route('/student')]
class StudentCourseController extends AbstractController
{
    #[Route('/courses', name: 'front_course_list')]
    public function listCourses(CourseRepository $courseRepository): Response
    {
        $courses = $courseRepository->findAll();
        return $this->render('front/course/list.html.twig', [
            'courses' => $courses,
        ]);
    }

  #[Route('/course/{id}/quizzes', name: 'front_course_quizzes')]
public function courseQuizzes(
    Course $course,
    Request $request,
    QuizRepository $quizRepository
): Response {

    $action = $request->query->get('action'); // search ou sort

    $filters = [
        'title' => null,
        'type' => null,
        'sort' => null,
        'order' => null,
    ];

    if ($action === 'search') {
        $filters['title'] = $request->query->get('title');
        $filters['type']  = $request->query->get('type');
    }

    if ($action === 'sort') {
        $filters['sort']  = $request->query->get('sort');
        $filters['order'] = $request->query->get('order', 'asc');
    }

    $quizzes = $quizRepository->searchByCourse($course, $filters);

    return $this->render('front/quiz/list.html.twig', [
        'course' => $course,
        'quizzes' => $quizzes,
        'filters' => $filters,
    ]);
}

}



