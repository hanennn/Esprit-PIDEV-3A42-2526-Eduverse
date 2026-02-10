<?php

namespace App\Controller\Back;

use App\Entity\Chapitres;
use App\Form\ChapitresType;
use App\Repository\ChapitresRepository;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back/chapitres')]
final class ChapitresController extends AbstractController
{
   #[Route('/', name: 'app_chapitres_index', methods: ['GET', 'POST'])]
public function index(ChapitresRepository $chapitresRepository, Request $request, EntityManagerInterface $entityManager): Response
{
    $chapitre = new Chapitres();
    $form = $this->createForm(ChapitresType::class, $chapitre);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $file = $form->get('contenuChap')->getData();
        if ($file) {
            $filename = uniqid() . '.' . $file->guessExtension();
            $file->move($this->getParameter('uploads_directory'), $filename);
            $chapitre->setContenuChap($filename);
        }

        $entityManager->persist($chapitre);
        $entityManager->flush();

        return $this->redirectToRoute('app_chapitres_index');
    }

    $chapitres = $chapitresRepository->findAll();

    return $this->render('back/chapitres/index.html.twig', [
        'chapitres' => $chapitres,
        'form' => $form->createView(),
    ]);
}


    #[Route('/new/{course_id}', name: 'app_chapitres_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, int $course_id, CoursRepository $coursRepo, ChapitresRepository $chapitresRepository): Response
{
    $chapitre = new Chapitres();
    $cours = $coursRepo->find($course_id);
    $chapitre->setCours($cours);

    $form = $this->createForm(ChapitresType::class, $chapitre);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $file = $form->get('contenuChap')->getData();
        if ($file) {
            $filename = uniqid() . '.' . $file->guessExtension();
            $file->move($this->getParameter('uploads_directory'), $filename);
            $chapitre->setContenuChap($filename);
        }

        $entityManager->persist($chapitre);
        $entityManager->flush();

        return $this->redirectToRoute('app_chapitres_index');
    }

    return $this->render('back/chapitres/index.html.twig', [
        'chapitres' => $chapitresRepository->findAll(),
        'chapitre' => $chapitre,
        'form' => $form->createView()
    ]);
}


    #[Route('/show/{id}', name: 'app_chapitres_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Chapitres $chapitre, Request $request): Response
    {
        $session = $request->getSession();
        $consultet = $session->get('consultet', 0);
        $consultet++;
        $session->set('consultet', $consultet);

        return $this->render('back/chapitres/show.html.twig', [
            'chapitre' => $chapitre,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_chapitres_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Chapitres $chapitre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChapitresType::class, $chapitre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('contenuChap')->getData();
            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('uploads_directory'), $filename);
                $chapitre->setContenuChap($filename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_chapitres_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/chapitres/edit.html.twig', [
            'chapitre' => $chapitre,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_chapitres_delete', methods: ['POST'])]
    public function delete(Request $request, Chapitres $chapitre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$chapitre->getId(), $request->request->get('_token'))) {
            $entityManager->remove($chapitre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_chapitres_index', [], Response::HTTP_SEE_OTHER);
    }
}
