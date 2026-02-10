<?php

namespace App\Controller\Back;

use App\Entity\Cours;
use App\Entity\Chapitres;
use App\Form\CoursType;
use App\Form\ChapitresType;
use App\Repository\ChapitresRepository;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/back/cours')]
final class CoursController extends AbstractController
{
    #[Route(name: 'app_cours_index', methods: ['GET', 'POST'])]
public function index(CoursRepository $coursRepository, Request $request, EntityManagerInterface $entityManager): Response
{
    $search = $request->query->get('search', '');
    $search_criteria = $request->query->get('search_criteria', 'titre'); 
    $sort = $request->query->get('sort', 'titre'); 

    $cours = $coursRepository->searchAndSortBack($search, $search_criteria, $sort);

    // 🔹 Créer un formulaire vide pour chaque cours (chapitre)
    $forms = [];
    foreach ($cours as $cour) {
        $chapitre = new Chapitres();
        $chapitre->setCours($cour);
        $forms[$cour->getId()] = $this->createForm(ChapitresType::class, $chapitre)->createView();
    }

    // 🔹 Créer le formulaire "Ajouter un cours"
    $newCourse = new Cours();
    $formCours = $this->createForm(CoursType::class, $newCourse);
    $formCours->handleRequest($request);

    // Si le formulaire Ajouter un cours est soumis
    if ($formCours->isSubmitted() && $formCours->isValid()) {
        $entityManager->persist($newCourse);
        $entityManager->flush();

        return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('back/cours/index.html.twig', [
        'cours' => $cours,
        'search' => $search,
        'search_criteria' => $search_criteria,
        'sort' => $sort,
        'forms' => $forms,
        'formCours' => $formCours->createView(), // 🔹 on passe le formulaire ici
    ]);
}


    #[Route('/{id}', name: 'app_cours_show', methods: ['GET'])]
    public function show(Cours $cour): Response
    {
        return $this->render('back/cours/show.html.twig', [
            'cour' => $cour,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cours_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CoursType::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/cours/edit.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cours_delete', methods: ['POST'])]
    public function delete(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cour->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cour);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
    }
}
