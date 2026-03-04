<?php
namespace App\Controller;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back/question')]
class QuestionController extends AbstractController
{
    #[Route('/add/{quizId}', name:'back_quiz_add_questions')]
    public function addQuestions(int $quizId, EntityManagerInterface $em, Request $request): Response // ✅ ajouté
    {
        $quiz = $em->getRepository(Quiz::class)->find($quizId);
        if (!$quiz) {
            throw $this->createNotFoundException("Quiz introuvable");
        }

        $question = new Question();
        $question->setQuiz($quiz);

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($question);
            $em->flush();

            $this->addFlash('success','Question ajoutée avec succès !');
         $quiz = $question->getQuiz();

if (!$quiz instanceof Quiz) {
    throw $this->createNotFoundException('Quiz associé introuvable.');
}

return $this->redirectToRoute('back_quiz_add_questions', ['quizId' => $quiz->getId()]);
        }

        return $this->render('NewQuestion.html.twig', [
            'form' => $form->createView(),
            'quiz' => $quiz
        ]);
    }
 #[Route('/edit/{id}', name:'back_question_edit')]
public function editQuestion(Question $question, Request $request, EntityManagerInterface $em): Response
{
    $form = $this->createForm(QuestionType::class, $question);
    $form->handleRequest($request);

    $quiz = $question->getQuiz();  // Ça peut être null ici selon Doctrine

    if ($quiz === null) {
        throw $this->createNotFoundException('Quiz associé introuvable.');
    }

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        $this->addFlash('success', 'Question modifiée avec succès !');

        return $this->redirectToRoute('back_quiz_add_questions', [
            'quizId' => $quiz->getId()
        ]);
    }

    return $this->render('NewQuestion.html.twig', [
        'form' => $form->createView(),
        'quiz' => $quiz
    ]);
}
#[Route('/delete/{id}', name:'back_question_delete', methods:['POST'])]
public function deleteQuestion(Request $request, Question $question, EntityManagerInterface $em): Response
{
    $token = $request->request->get('_token');
    $token = is_string($token) ? $token : null;

    $quiz = $question->getQuiz();

    if ($quiz === null) {
        throw $this->createNotFoundException('Quiz associé introuvable.');
    }

    if ($this->isCsrfTokenValid('delete' . $question->getId(), $token)) {
        $em->remove($question);
        $em->flush();
        $this->addFlash('success', 'Question supprimée avec succès !');
    }

    return $this->redirectToRoute('back_quiz_add_questions', [
        'quizId' => $quiz->getId()
    ]);
}
}


