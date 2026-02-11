<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Repository\UserRepository;
use App\Repository\QuizRepository;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(
        Request $request, 
        UserRepository $userRepository, 
        QuizRepository $quizRepository,
        CoursRepository $coursRepository,
        PaginatorInterface $paginator
    ): Response
    {
        // Make sure user is logged in
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('warning', 'Veuillez vous connecter pour accéder à votre profil.');
            return $this->redirectToRoute('app_login');
        }
        
        $roles = $user->getRoles();
        
        // Check ADMIN or SUPER_ADMIN first (highest priority)
        if (in_array('ROLE_ADMIN', $roles) || in_array('ROLE_SUPER_ADMIN', $roles)) {
            // ----------------------------
            // USERS (with pagination)
            // ----------------------------
            $searchQuery = $request->query->get('search', '');
            
            // Build user query
            $queryBuilder = $userRepository->createQueryBuilder('u');
            
            if ($searchQuery) {
                $queryBuilder->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search')
                    ->setParameter('search', '%' . $searchQuery . '%');
            }
            
            $queryBuilder->orderBy('u.DateInscription', 'DESC');
            
            // Paginate users
            $users = $paginator->paginate(
                $queryBuilder,
                $request->query->getInt('page', 1),
                10
            );
            
            $totalUsers = $userRepository->count([]);
            
            // Get instructors count
            $totalInstructors = $userRepository->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->where('u.roles LIKE :role')
                ->setParameter('role', '%ROLE_TEACHER%')
                ->getQuery()
                ->getSingleScalarResult();
            
            // ----------------------------
            // QUIZZES & CERTIFICATIONS
            // ----------------------------
            $quizzes = $quizRepository->findAll();
            $totalQuizzes = count($quizzes);
            
            $totalCertifications = 0;
            $ranking = [];
            
            foreach ($quizzes as $quiz) {
                $certCount = $quiz->getCertifications() ? count($quiz->getCertifications()) : 0;
                $totalCertifications += $certCount;
                
                $ranking[] = [
                    'titre' => $quiz->getTitre(),
                    'certCount' => $certCount
                ];
            }
            
            // Sort ranking by certification count (descending)
            usort($ranking, fn($a, $b) => $b['certCount'] <=> $a['certCount']);
            
            // Calculate average score
            $averageScore = $totalQuizzes > 0 ? ($totalCertifications / $totalQuizzes) * 10 : 0;
            
            // ----------------------------
            // COURSES DATA
            // ----------------------------
            $courseSearch = $request->query->get('search', '');
            $courseSearchCriteria = $request->query->get('search_criteria', 'titre');
            $courseSort = $request->query->get('sort', 'id');
            
            // Get all courses with search and sort
            $allCourses = $coursRepository->searchAndSortBack($courseSearch, $courseSearchCriteria, $courseSort);
            
            $totalCourses = count($allCourses);
            
            // Count unique instructors who created courses
            $instructorIds = [];
            foreach ($allCourses as $course) {
                if ($course->getCreateur()) {
                    $instructorIds[$course->getCreateur()->getId()] = true;
                }
            }
            $activeInstructors = count($instructorIds);
            
            // Count unique languages
            $languages = [];
            foreach ($allCourses as $course) {
                $languages[$course->getLangueCours()] = true;
            }
            $totalLanguages = count($languages);
            
            // Count unique subjects
            $subjects = [];
            foreach ($allCourses as $course) {
                $subjects[$course->getMatiereCours()] = true;
            }
            $totalSubjects = count($subjects);
            
            // ----------------------------
            // RECENT ACTIVITIES (sample data - you can enhance this)
            // ----------------------------
            $recentActivities = [
                [
                    'type' => 'success',
                    'icon' => 'check',
                    'title' => 'Nouvel utilisateur',
                    'description' => 'Un nouveau compte a été créé',
                    'time' => 'Il y a 5 minutes'
                ],
                [
                    'type' => 'primary',
                    'icon' => 'book',
                    'title' => 'Nouveau cours',
                    'description' => 'Un cours a été ajouté',
                    'time' => 'Il y a 1 heure'
                ],
            ];
            
            // ----------------------------
            // POPULAR COURSES (sample data - you can enhance this)
            // ----------------------------
            $popularCourses = [
                [
                    'title' => 'Symfony',
                    'instructor' => [
                        'title' => 'Mr',
                        'firstName' => 'Karim',
                        'lastName' => 'Ben Ali'
                    ],
                    'enrolledCount' => 120,
                    'averageRating' => 4.8,
                    'isActive' => true
                ],
                [
                    'title' => 'Machine Learning',
                    'instructor' => [
                        'title' => 'Mme',
                        'firstName' => 'Asma',
                        'lastName' => 'Trabelsi'
                    ],
                    'enrolledCount' => 90,
                    'averageRating' => 4.6,
                    'isActive' => true
                ]
            ];
            
            // ----------------------------
            // CHART DATA
            // ----------------------------
            $chartData = [
                'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
                'datasets' => [
                    [
                        'label' => 'Inscriptions',
                        'data' => [10, 20, 15, 25, 30, 40],
                        'borderColor' => '#ffbc3b',
                        'backgroundColor' => 'rgba(255, 188, 59, 0.1)',
                        'tension' => 0.4,
                        'borderWidth' => 2
                    ]
                ]
            ];
            
            return $this->render('backoffice.html.twig', [
                'admin' => $user,
                'stats' => [
                    'totalUsers' => $totalUsers,
                    'usersGrowth' => 0,
                    'activeCourses' => $totalCourses,
                    'newCourses' => 1,
                    'totalInstructors' => $totalInstructors,
                    'newInstructors' => 1,
                    'scholarshipRequests' => 4,
                    'pendingScholarships' => 2,
                ],
                'recentActivities' => $recentActivities,
                'popularCourses' => $popularCourses,
                'users' => $users,
                'searchQuery' => $searchQuery,
                'statusFilter' => '',
                'currentFilter' => 'all',
                'userCounts' => [
                    'total' => $totalUsers,
                    'active' => $totalUsers,
                    'inactive' => 0,
                    'recent' => 2,
                ],
                'chartData' => $chartData,
                // Quiz-related data
                'totalQuizzes' => $totalQuizzes,
                'totalCertifications' => $totalCertifications,
                'averageScore' => round($averageScore, 2),
                'quizs' => $quizzes,
                'ranking' => $ranking,
                'filters' => [
                    'title' => '',
                    'type' => '',
                    'course' => '',
                ],
                // Course-related data
                'allCourses' => $allCourses,
                'totalCourses' => $totalCourses,
                'activeInstructors' => $activeInstructors,
                'totalLanguages' => $totalLanguages,
                'totalSubjects' => $totalSubjects,
                'courseFilters' => [
                    'search' => $courseSearch,
                    'criteria' => $courseSearchCriteria,
                    'sort' => $courseSort,
                ],
            ]);
        }
        // Check TEACHER
        elseif (in_array('ROLE_TEACHER', $roles)) {
            // ========== QUIZZES ==========
            $instructorQuizzes = $quizRepository->findByInstructor($user);
            
            // Calculate quiz statistics
            $totalCertifications = 0;
            $totalScore = 0;
            $totalAttempts = 0;
            
            foreach ($instructorQuizzes as $quiz) {
                $certs = $quiz->getCertifications();
                $certCount = count($certs);
                $totalCertifications += $certCount;
                $totalAttempts += $certCount;
                
                foreach ($certs as $cert) {
                    $totalScore += $cert->getScore();
                }
            }
            
            $averageScore = $totalAttempts > 0 ? round($totalScore / $totalAttempts, 2) : 0;
            
            $quizStats = [
                'totalQuizzes' => count($instructorQuizzes),
                'totalAttempts' => $totalAttempts,
                'averageScore' => $averageScore,
                'totalCertifications' => $totalCertifications
            ];
            
            // Format quizzes for display
            $formattedQuizzes = [];
            foreach ($instructorQuizzes as $quiz) {
                $certs = $quiz->getCertifications();
                $certCount = count($certs);
                
                $quizScore = 0;
                $passedCount = 0;
                foreach ($certs as $cert) {
                    $quizScore += $cert->getScore();
                    if ($cert->getScore() >= 50) {
                        $passedCount++;
                    }
                }
                
                $formattedQuizzes[] = [
                    'id' => $quiz->getId(),
                    'title' => $quiz->getTitre(),
                    'questionCount' => count($quiz->getQuestions()),
                    'attemptCount' => $certCount,
                    'averageScore' => $certCount > 0 ? round($quizScore / $certCount, 2) : 0,
                    'passRate' => $certCount > 0 ? round(($passedCount / $certCount) * 100, 2) : 0,
                    'createdAt' => new \DateTime(),
                    'isArchived' => false,
                ];
            }
            
            // ========== COURSES ==========
            $allCourses = $coursRepository->searchAndSort(null, null, null);
            $instructorCourses = [];

            // Filter courses where createur matches the current user
            foreach ($allCourses as $cours) {
                if ($cours->getCreateur() && $cours->getCreateur()->getId() == $user->getId()) {
                    $instructorCourses[] = $cours;
                }
            }

            // Format courses for display
            $formattedCourses = [];
            foreach ($instructorCourses as $cours) {
                $formattedCourses[] = [
                    'id' => $cours->getId(),
                    'title' => $cours->getTitreCours(),
                    'niveau' => $cours->getNivCours(),
                    'matiere' => $cours->getMatiereCours(),
                    'langue' => $cours->getLangueCours(),
                    'description' => $cours->getDescription(),
                    'enrolledStudents' => 0, // TODO: Calculate from enrollment entity
                    'completionRate' => 0, // TODO: Calculate from student progress
                    'averageRating' => 0, // TODO: Calculate from reviews
                    'createdAt' => new \DateTime(),
                    'isArchived' => false,
                ];
            }
            
            return $this->render('Formateur.html.twig', [
                'instructor' => $user,
                'courses' => $formattedCourses,
                'quizzes' => $formattedQuizzes,
                'quizStats' => $quizStats,
                'reviewStats' => [
                    'averageRating' => 0,
                    'totalReviews' => 0,
                    'recommendationRate' => 0,
                ],
                'recentReviews' => [],
                'stats' => [
                    'activeCourses' => count($formattedCourses),
                    'totalStudents' => 0,
                    'totalReviews' => 0,
                    'averageRating' => 0,
                    'averageSuccessRate' => $averageScore,
                ],
                'unreadMessages' => 0,
            ]);
        }
        // Default to STUDENT
        else {
            return $this->render('User.html.twig', [
                'user' => $user,
                'enrollments' => [],
                'quizStats' => [
                    'totalCompleted' => 0,
                    'averageScore' => 0,
                ],
                'recentQuizResults' => [],
                'badges' => [],
                'achievementBadges' => [],
                'donationBadges' => [],
                'milestoneBadges' => [],
                'badgePoints' => 0,
                'donations' => [],
                'donationStats' => [
                    'totalAmount' => 0,
                    'donorLevel' => 'Débutant',
                    'badgesEarned' => 0,
                ],
                'stats' => [
                    'enrolledCourses' => 0,
                    'completedCourses' => 0,
                    'streakDays' => 0,
                    'completionRate' => 0,
                ],
            ]);
        }
    }

    // ==================== INSTRUCTOR COURSE MANAGEMENT ====================
    
    /**
     * Update course - Simple form submission (like user info update)
     */
    #[Route('/instructor/course/{id}/update', name: 'app_instructor_update_course', methods: ['POST'])]
    public function updateCourse(
        Request $request,
        Cours $cours,
        EntityManagerInterface $em
    ): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_login');
        }

        if ($cours->getCreateur()->getId() !== $user->getId()) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier ce cours.');
            return $this->redirectToRoute('app_profile');
        }

        try {
            // Get form data
            $titre = $request->request->get('titre_cours');
            $niveau = $request->request->get('niv_cours');
            $matiere = $request->request->get('matiere_cours');
            $langue = $request->request->get('langue_cours');
            $description = $request->request->get('description');
            
            // Update course
            if ($titre) {
                $cours->setTitreCours($titre);
            }
            if ($niveau) {
                $cours->setNivCours($niveau);
            }
            if ($matiere) {
                $cours->setMatiereCours($matiere);
            }
            if ($langue) {
                $cours->setLangueCours($langue);
            }
            if ($description !== null) {
                $cours->setDescription($description);
            }

            $em->flush();

            $this->addFlash('success', 'Cours mis à jour avec succès.');

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_profile');
    }

    /**
     * Delete course - Simple form submission
     */
    #[Route('/instructor/course/{id}/delete', name: 'app_instructor_delete_course', methods: ['POST'])]
    public function deleteCourse(
        Cours $cours,
        EntityManagerInterface $em
    ): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_login');
        }

        if ($cours->getCreateur()->getId() !== $user->getId()) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer ce cours.');
            return $this->redirectToRoute('app_profile');
        }

        try {
            $em->remove($cours);
            $em->flush();

            $this->addFlash('success', 'Cours supprimé avec succès.');

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_profile');
    }

    // ==================== ADMIN COURSE MANAGEMENT ====================

    /**
     * Admin update course from backoffice
     */
    #[Route('/admin/course/{id}/update', name: 'back_course_update', methods: ['POST'])]
    public function adminUpdateCourse(
        Request $request,
        Cours $cours,
        EntityManagerInterface $em
    ): Response
    {
        // Check if user is admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            // Get form data
            $titre = $request->request->get('titre_cours');
            $niveau = $request->request->get('niv_cours');
            $matiere = $request->request->get('matiere_cours');
            $langue = $request->request->get('langue_cours');
            $description = $request->request->get('description');
            
            // Update course
            if ($titre) {
                $cours->setTitreCours($titre);
            }
            if ($niveau) {
                $cours->setNivCours($niveau);
            }
            if ($matiere) {
                $cours->setMatiereCours($matiere);
            }
            if ($langue) {
                $cours->setLangueCours($langue);
            }
            if ($description !== null) {
                $cours->setDescription($description);
            }

            $em->flush();

            $this->addFlash('success', 'Cours mis à jour avec succès.');

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_profile', ['section' => 'courses']);
    }

    /**
     * Admin delete course from backoffice
     */
    #[Route('/admin/course/{id}/delete', name: 'back_course_delete', methods: ['POST'])]
    public function adminDeleteCourse(
        Cours $cours,
        EntityManagerInterface $em
    ): Response
    {
        // Check if user is admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $em->remove($cours);
            $em->flush();

            $this->addFlash('success', 'Cours supprimé avec succès.');

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_profile', ['section' => 'courses']);
    }
}
