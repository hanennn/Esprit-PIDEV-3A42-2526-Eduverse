<?php

namespace App\Service;

use App\Entity\Question;

final class QuestionManager
{
    /**
     * Règles métier:
     * 1) question obligatoire (non vide)
     * 2) points > 0
     * 3) reponses: au moins 2 réponses
     * 4) exactement 1 réponse avec correct=true
     *
     * @throws \InvalidArgumentException
     */
    public function validate(Question $question): bool
    {
        $text = trim($question->getQuestion());
        if ($text === '') {
            throw new \InvalidArgumentException('La question est obligatoire');
        }

        if ($question->getPoints() <= 0) {
            throw new \InvalidArgumentException('Les points doivent être > 0');
        }

        $reponses = $question->getReponses();
        if (count($reponses) < 2) {
            throw new \InvalidArgumentException('Il faut au moins 2 réponses');
        }

        $correctCount = 0;

        foreach ($reponses as $i => $r) {
            // PHPStan sait déjà que $r = array{texte: string, correct: bool}
            if (trim($r['texte']) === '') {
                throw new \InvalidArgumentException("Réponse $i: texte obligatoire");
            }

            if ($r['correct'] === true) {
                $correctCount++;
            }
        }

        if ($correctCount !== 1) {
            throw new \InvalidArgumentException('Il doit y avoir exactement 1 seule réponse correcte');
        }

        return true;
    }
}