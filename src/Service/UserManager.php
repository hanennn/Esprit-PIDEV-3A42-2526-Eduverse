<?php
namespace App\Service;

use App\Entity\User;

class UserManager
{
    public function validate(User $user): bool
    {
        if (empty($user->getUsername())) {
            throw new \InvalidArgumentException("Le numéro d'inscription (Username) est obligatoire.");
        }

        if (empty($user->getNom())) {
            throw new \InvalidArgumentException('Le nom est obligatoire.');
        }

        if (empty($user->getPrenom())) {
            throw new \InvalidArgumentException('Le prénom est obligatoire.');
        }

        if (empty($user->getEmail())) {
            throw new \InvalidArgumentException("L'email est obligatoire.");
        }

        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("L'email n'est pas valide.");
        }

        
        if (strlen((string) $user->getPassword()) < 8) {
            throw new \InvalidArgumentException('Le mot de passe doit contenir au moins 8 caractères.');
        }

        return true;
    }
}