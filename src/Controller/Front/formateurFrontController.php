<?php

namespace App\Controller\Front;

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

#[Route('/front')]
class formateurFrontController extends AbstractController
{
    #[Route('/cours', name: 'front_cours_index', methods: ['GET', 'POST'])]
    public function index(
    Request $request,
    EntityManagerInterface $em,
    CoursRepository $coursRepo
): Response {
    // 🔍 Paramètres de recherche et tri depuis l'URL (GET)
    $search = $request->query->get('search');
    $searchCriteria = $request->query->get('search_criteria', 'titre');
    $sort = $request->query->get('sort', 'titre');

    // 🔹 Récupérer les cours selon recherche et tri
    $coursList = $coursRepo->searchAndSort($search, $searchCriteria, $sort);

    // ➕ Formulaire ajout cours
    $cours = new Cours();
    $formCours = $this->createForm(CoursType::class, $cours);
    $formCours->handleRequest($request);

    if ($formCours->isSubmitted() && $formCours->isValid()) {
        $em->persist($cours);
        $em->flush();

        return $this->redirectToRoute('front_cours_index');
    }

    // 🔹 Formulaires de chapitres pour chaque cours
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

            // 📎 Upload fichier
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

        return $this->render('front/chapitres/new.html.twig', [
            'cours' => $cours,
            'formChapitre' => $formChapitre->createView(),
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

        return $this->render('front/chapitres/edit.html.twig', [
            'form' => $form->createView(),
            'chapitre' => $chapitre,
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
}
