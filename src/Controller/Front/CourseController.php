<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CourseController extends AbstractController
{
    #[Route('/test/calendar', name: 'front_test_calendar')]
    public function testCalendar(): Response
    {
        // On n'a pas besoin de vrais cours pour tester le calendrier
        $courses = [];

        return $this->render('front/course/testcalendar.html.twig', [
            'courses' => $courses,
        ]);
    }
}
