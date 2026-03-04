<?php

namespace App\Service;

use App\Entity\Chapitre;
use App\Entity\Chapitres;

class ChapitreManager
{
    public function validate(Chapitres $chapitre): bool
    {
        if (empty($chapitre->getTitreChap())) {
            throw new \InvalidArgumentException('Le titre du chapitre est obligatoire');
        }

        if ($chapitre->getCours() === null) {
            throw new \InvalidArgumentException('Un chapitre doit être associé à un cours');
        }

        return true;
    }
}