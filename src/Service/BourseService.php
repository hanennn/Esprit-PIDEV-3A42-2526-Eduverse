<?php

namespace App\Service;

use App\Entity\Bourse;

class BourseService
{
    /**
     * Valide les contrôles de saisie d'une Bourse.
     * Retourne un tableau d'erreurs (vide si tout est valide).
     *
     * @return string[]
     */
    public function validerBourse(Bourse $bourse): array
    {
        $erreurs = [];

        // --- Titre ---
        if (empty($bourse->getTitre())) {
            $erreurs[] = "Le titre est obligatoire.";
        } elseif (mb_strlen($bourse->getTitre()) > 255) {
            $erreurs[] = "Le titre ne doit pas dépasser 255 caractères.";
        } elseif (mb_strlen(trim($bourse->getTitre())) < 3) {
            $erreurs[] = "Le titre doit contenir au moins 3 caractères.";
        }

        // --- Description ---
        if (empty($bourse->getDescription())) {
            $erreurs[] = "La description est obligatoire.";
        } elseif (mb_strlen(trim($bourse->getDescription())) < 10) {
            $erreurs[] = "La description doit contenir au moins 10 caractères.";
        }

        // --- Montant ---
        if ($bourse->getMontant() === null) {
            $erreurs[] = "Le montant est obligatoire.";
        } elseif ($bourse->getMontant() <= 0) {
            $erreurs[] = "Le montant doit être supérieur à 0.";
        } elseif ($bourse->getMontant() > 1_000_000) {
            $erreurs[] = "Le montant ne peut pas dépasser 1 000 000.";
        }

        // --- Date d'attribution ---
        if ($bourse->getDateAttribution() === null) {
            $erreurs[] = "La date d'attribution est obligatoire.";
        } elseif ($bourse->getDateAttribution() <= new \DateTime('today')) {
            $erreurs[] = "La date d'attribution doit être dans le futur.";
        }

        // --- Date de fin ---
        if ($bourse->getDateFin() === null) {
            $erreurs[] = "La date de fin est obligatoire.";
        } elseif ($bourse->getDateAttribution() !== null && $bourse->getDateFin() <= $bourse->getDateAttribution()) {
            $erreurs[] = "La date de fin doit être postérieure à la date d'attribution.";
        }

        // --- Image (optionnelle mais si présente, extension valide) ---
        if ($bourse->getImage() !== null && $bourse->getImage() !== '') {
            $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower(pathinfo($bourse->getImage(), PATHINFO_EXTENSION));
            if (!in_array($extension, $extensionsAutorisees, true)) {
                $erreurs[] = "L'image doit être au format : " . implode(', ', $extensionsAutorisees) . ".";
            }
        }

        return $erreurs;
    }

    /**
     * Filtre une liste de bourses selon des critères optionnels.
     *
     * @param Bourse[] $bourses
     * @param array{
     *   titre?: string,
     *   montantMin?: float|int,
     *   montantMax?: float|int,
     *   dateDebut?: \DateTimeInterface,
     *   dateFin?: \DateTimeInterface,
     *   enCours?: bool
     * } $criteres
     *
     * @return Bourse[]
     */
    public function filtrerBourses(array $bourses, array $criteres): array
    {
        return array_values(array_filter($bourses, function (Bourse $bourse) use ($criteres) {

            // Filtre par titre (recherche partielle)
            if (!empty($criteres['titre'])) {
                $titreBourse = $bourse->getTitre() ?? '';
                if (mb_stripos($titreBourse, $criteres['titre']) === false) {
                    return false;
                }
            }

            // Filtre par montant minimum
            if (isset($criteres['montantMin']) && $bourse->getMontant() < $criteres['montantMin']) {
                return false;
            }

            // Filtre par montant maximum
            if (isset($criteres['montantMax']) && $bourse->getMontant() > $criteres['montantMax']) {
                return false;
            }

            // Filtre par date d'attribution >= dateDebut
            if (isset($criteres['dateDebut']) && $bourse->getDateAttribution() < $criteres['dateDebut']) {
                return false;
            }

            // Filtre par date de fin <= dateFin
            if (isset($criteres['dateFin']) && $bourse->getDateFin() > $criteres['dateFin']) {
                return false;
            }

            // Filtre bourses en cours (aujourd'hui est entre dateAttribution et dateFin)
            if (!empty($criteres['enCours'])) {
                $maintenant = new \DateTime();
                if ($bourse->getDateAttribution() > $maintenant || $bourse->getDateFin() < $maintenant) {
                    return false;
                }
            }

            return true;
        }));
    }
}