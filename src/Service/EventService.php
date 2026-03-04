<?php

namespace App\Service;

use App\Entity\Event;

class EventService
{
    public const TYPES_VALIDES = ['webinaire', 'atelier', 'challenge'];
    public const NIVEAUX_VALIDES = ['debutant', 'intermediaire', 'avance'];

    /**
     * Valide les contrôles de saisie d'un Event.
     * Retourne un tableau d'erreurs (vide si tout est valide).
     *
     * @return string[]
     */
    public function validerEvent(Event $event): array
    {
        $erreurs = [];

        // --- Titre ---
        if (empty($event->getTitre())) {
            $erreurs[] = "Le titre est obligatoire.";
        } elseif (mb_strlen($event->getTitre()) > 255) {
            $erreurs[] = "Le titre ne doit pas dépasser 255 caractères.";
        } elseif (mb_strlen(trim($event->getTitre())) < 3) {
            $erreurs[] = "Le titre doit contenir au moins 3 caractères.";
        }

        // --- Description ---
        if (empty($event->getDescription())) {
            $erreurs[] = "La description est obligatoire.";
        } elseif (mb_strlen(trim($event->getDescription())) < 10) {
            $erreurs[] = "La description doit contenir au moins 10 caractères.";
        }

        // --- Type ---
        if (empty($event->getType())) {
            $erreurs[] = "Le type est obligatoire.";
        } elseif (!in_array($event->getType(), self::TYPES_VALIDES, true)) {
            $erreurs[] = "Le type sélectionné n'est pas valide. Valeurs acceptées : " . implode(', ', self::TYPES_VALIDES) . ".";
        }

        // --- Niveau (optionnel mais si présent doit être valide) ---
        if ($event->getNiveau() !== null && $event->getNiveau() !== '') {
            if (!in_array($event->getNiveau(), self::NIVEAUX_VALIDES, true)) {
                $erreurs[] = "Le niveau sélectionné n'est pas valide. Valeurs acceptées : " . implode(', ', self::NIVEAUX_VALIDES) . ".";
            }
        }

        // --- Lien webinaire (obligatoire si type = webinaire) ---
        if ($event->getType() === 'webinaire') {
            if (empty($event->getLienWebinaire())) {
                $erreurs[] = "Le lien du webinaire est obligatoire pour un événement de type webinaire.";
            } elseif (!filter_var($event->getLienWebinaire(), FILTER_VALIDATE_URL)) {
                $erreurs[] = "Le lien du webinaire doit être une URL valide.";
            }
        }

        // --- Date ---
        if ($event->getDate() === null) {
            $erreurs[] = "La date est obligatoire.";
        }

        // --- Heure de début ---
        if ($event->getHeureDeb() === null) {
            $erreurs[] = "L'heure de début est obligatoire.";
        }

        // --- Heure de fin ---
        if ($event->getHeureFin() === null) {
            $erreurs[] = "L'heure de fin est obligatoire.";
        }

        // --- Cohérence heureFin > heureDeb ---
        if ($event->getHeureDeb() !== null && $event->getHeureFin() !== null) {
            if ($event->getHeureFin() <= $event->getHeureDeb()) {
                $erreurs[] = "L'heure de fin doit être postérieure à l'heure de début.";
            }
        }

        // --- Image (optionnelle mais extension valide si présente) ---
        if ($event->getImage() !== null && $event->getImage() !== '') {
            $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower(pathinfo($event->getImage(), PATHINFO_EXTENSION));
            if (!in_array($extension, $extensionsAutorisees, true)) {
                $erreurs[] = "L'image doit être au format : " . implode(', ', $extensionsAutorisees) . ".";
            }
        }

        return $erreurs;
    }

    /**
     * Filtre une liste d'événements selon des critères optionnels.
     *
     * @param Event[] $events
     * @param array{
     *   titre?: string,
     *   type?: string,
     *   niveau?: string,
     *   dateMin?: \DateTimeInterface,
     *   dateMax?: \DateTimeInterface,
     *   aVenir?: bool
     * } $criteres
     *
     * @return Event[]
     */
    public function filtrerEvents(array $events, array $criteres): array
    {
        return array_values(array_filter($events, function (Event $event) use ($criteres) {

            // Filtre par titre (recherche partielle)
            if (!empty($criteres['titre'])) {
                $titreEvent = $event->getTitre() ?? '';
                if (mb_stripos($titreEvent, $criteres['titre']) === false) {
                    return false;
                }
            }

            // Filtre par type exact
            if (!empty($criteres['type'])) {
                if ($event->getType() !== $criteres['type']) {
                    return false;
                }
            }

            // Filtre par niveau exact
            if (!empty($criteres['niveau'])) {
                if ($event->getNiveau() !== $criteres['niveau']) {
                    return false;
                }
            }

            // Filtre par date minimum
            if (isset($criteres['dateMin']) && $event->getDate() < $criteres['dateMin']) {
                return false;
            }

            // Filtre par date maximum
            if (isset($criteres['dateMax']) && $event->getDate() > $criteres['dateMax']) {
                return false;
            }

            // Filtre événements à venir
            if (!empty($criteres['aVenir'])) {
                $aujourdhui = new \DateTime('today');
                if ($event->getDate() < $aujourdhui) {
                    return false;
                }
            }

            return true;
        }));
    }
}