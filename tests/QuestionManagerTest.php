<?php

namespace App\Tests\Service;

use App\Entity\Question;
use App\Service\QuestionManager;
use PHPUnit\Framework\TestCase;

class QuestionManagerTest extends TestCase
{
    public function testValidQuestion(): void
    {
        $q = new Question();
        $q->setQuestion('Quelle est la capitale de la Tunisie ?');
        $q->setPoints(10);
        $q->setReponses([
            ['texte' => 'Tunis', 'correct' => true],
            ['texte' => 'Sfax', 'correct' => false],
        ]);

        $manager = new QuestionManager();
        $this->assertTrue($manager->validate($q));
    }

    public function testQuestionWithoutText(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $q = new Question();
        $q->setQuestion('');
        $q->setPoints(10);
        $q->setReponses([
            ['texte' => 'A', 'correct' => true],
            ['texte' => 'B', 'correct' => false],
        ]);

        (new QuestionManager())->validate($q);
    }

    public function testQuestionWithInvalidPoints(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $q = new Question();
        $q->setQuestion('Question test');
        $q->setPoints(0);
        $q->setReponses([
            ['texte' => 'A', 'correct' => true],
            ['texte' => 'B', 'correct' => false],
        ]);

        (new QuestionManager())->validate($q);
    }

    public function testQuestionWithNoCorrectAnswer(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $q = new Question();
        $q->setQuestion('Question test');
        $q->setPoints(10);
        $q->setReponses([
            ['texte' => 'A', 'correct' => false],
            ['texte' => 'B', 'correct' => false],
        ]);

        (new QuestionManager())->validate($q);
    }

    public function testQuestionWithTwoCorrectAnswers(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $q = new Question();
        $q->setQuestion('Question test');
        $q->setPoints(10);
        $q->setReponses([
            ['texte' => 'A', 'correct' => true],
            ['texte' => 'B', 'correct' => true],
        ]);

        (new QuestionManager())->validate($q);
    }
}