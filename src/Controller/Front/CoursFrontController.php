<?php

namespace App\Controller\Front;

use App\Entity\Cours;
use App\Repository\CoursRepository;
use App\Repository\ChapitresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse; //AJAX


#[Route('/front')]
class CoursFrontController extends AbstractController
{
    #[Route('/courses', name: 'courses')]
    public function index(CoursRepository $coursRepository, Request $request): Response
    {
    // Récupérer les paramètres GET
    $search = $request->query->get('search', '');
    $sort = $request->query->get('sort', '');
    $criteria = $request->query->get('criteria', 'titre');
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
public function show(Cours $cours, ChapitresRepository $chapitresRepository): Response
{
    // Récupère tous les chapitres du cours
    $chapitres = $chapitresRepository->findBy(
        ['cours' => $cours->getId()],
        ['ordre_chap' => 'ASC'] // tri par ordre si tu veux
    );

    return $this->render('front/course_show.html.twig', [
        'cours' => $cours,      // le cours
        'chapitres' => $chapitres, // la liste des chapitres
    ]);
}

    #[Route('/chapitre/{id}', name: 'chapitre_contenu')]
    public function showChapitre(ChapitresRepository $chapitreRepository, int $id, EntityManagerInterface $em): Response
    {
        $chapitre = $chapitreRepository->find($id);

        if (!$chapitre) {
            throw $this->createNotFoundException('Chapitre non trouvé');
        }
        if ($chapitre->getStatutChap() !== 'Ouvert') {
        $chapitre->setStatutChap('Ouvert');
        $em->flush();
    }

        return $this->render('front/chapitre_show.html.twig', [
            'chapitre' => $chapitre,
        ]);
    }


  //favoris
#[Route('/favorites/toggle/{id}', name: 'toggle_favorite', methods: ['POST'])]
public function toggleFavorite(int $id, Request $request): JsonResponse
{
    if (!$request->isXmlHttpRequest()) {
        return new JsonResponse(['success' => false, 'message' => 'Requête invalide'], 400);
    }

    $session = $request->getSession();
    $favorites = $session->get('favorites', []);

    if(in_array($id, $favorites)){
        // supprimer des favoris
        $favorites = array_diff($favorites, [$id]);
        $added = false;
    } else {
        // ajouter aux favoris
        $favorites[] = $id;
        $added = true;
    }

    $session->set('favorites', $favorites);

    return new JsonResponse(['success' => true, 'added' => $added]);
}



}

