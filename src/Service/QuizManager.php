<?php

namespace App\Service;

use App\Entity\Quiz;

final class QuizManager
{
    public function validate(Quiz $quiz): bool
    {
        $titre = $quiz->getTitre();
        if (!is_string($titre) || trim($titre) === '') {
            throw new \InvalidArgumentException('Le titre est obligatoire');
        }

        $duree = $quiz->getDuree();
        if (!is_int($duree) || $duree <= 0) {
            throw new \InvalidArgumentException('La durée doit être > 0');
        }

        $scoreMin = $quiz->getScoreMinimum();
        if ($scoreMin === null || (float) $scoreMin < 0) {
            throw new \InvalidArgumentException('Le score minimum doit être >= 0');
        }

        return true;
    }
    
}