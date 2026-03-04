<?php

namespace App\Service;

use App\Entity\Cours;

class CoursManager
{
    public function validate(Cours $cours): bool
    {
        if (empty($cours->getTitreCours())) {
            throw new \InvalidArgumentException('Le titre du cours est obligatoire');
        }

       if (empty($cours->getDescription())) {
            throw new \InvalidArgumentException('La description du cours est obligatoire');
        }

        return true;
    }
}