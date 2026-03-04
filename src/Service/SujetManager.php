<?php

namespace App\Service;

use App\Entity\Sujet;

class SujetManager
{
    /**
     * Règles métier:
     * 1) titre obligatoire, 5..255
     * 2) contenu obligatoire, >=10
     * 3) auteur obligatoire
     */
    public function validate(Sujet $sujet): bool
    {
        $titre = trim((string) $sujet->getTitre());
        if ($titre === '') {
            throw new \InvalidArgumentException('Le titre est obligatoire');
        }
        if (mb_strlen($titre) < 5) {
            throw new \InvalidArgumentException('Le titre doit contenir au moins 5 caractères');
        }
        if (mb_strlen($titre) > 255) {
            throw new \InvalidArgumentException('Le titre ne doit pas dépasser 255 caractères');
        }

        $contenu = trim((string) $sujet->getContenu());
        if ($contenu === '') {
            throw new \InvalidArgumentException('Le contenu est obligatoire');
        }
        if (mb_strlen($contenu) < 10) {
            throw new \InvalidArgumentException('Le contenu doit contenir au moins 10 caractères');
        }

        if ($sujet->getAuteur() === null) {
            throw new \InvalidArgumentException('L’auteur est obligatoire');
        }

        return true;
    }
}