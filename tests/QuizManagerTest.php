<?php

namespace App\Tests\Service;

use App\Entity\Quiz;
use App\Service\QuizManager;
use PHPUnit\Framework\TestCase;

class QuizManagerTest extends TestCase
{
    public function testValidQuiz(): void
    {
        $quiz = new Quiz();
        $quiz->setTitre('Quiz Symfony');
        $quiz->setDuree(10);
        $quiz->setScoreMinimum(5);

        $manager = new QuizManager();
        $this->assertTrue($manager->validate($quiz));
    }

    public function testQuizWithoutTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le titre est obligatoire');

        $quiz = new Quiz();
        $quiz->setTitre('');
        $quiz->setDuree(10);
        $quiz->setScoreMinimum(5);

        (new QuizManager())->validate($quiz);
    }

    public function testQuizWithZeroDuration(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La durée doit être > 0');

        $quiz = new Quiz();
        $quiz->setTitre('Quiz Test');
        $quiz->setDuree(0);
        $quiz->setScoreMinimum(5);

        (new QuizManager())->validate($quiz);
    }

    public function testQuizWithNegativeDuration(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $quiz = new Quiz();
        $quiz->setTitre('Quiz Test');
        $quiz->setDuree(-3);
        $quiz->setScoreMinimum(5);

        (new QuizManager())->validate($quiz);
    }

    public function testQuizWithNegativeScoreMinimum(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le score minimum doit être >= 0');

        $quiz = new Quiz();
        $quiz->setTitre('Quiz Test');
        $quiz->setDuree(10);
        $quiz->setScoreMinimum(-1);

        (new QuizManager())->validate($quiz);
    }
}