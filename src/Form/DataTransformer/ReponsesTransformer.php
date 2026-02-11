<?php
namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ReponsesTransformer implements DataTransformerInterface
{
    // Transforme le tableau PHP en JSON pour le formulaire
    public function transform($value)
    {
        if ($value === null) {
            return '';
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // Transforme le JSON du formulaire en tableau PHP
    public function reverseTransform($value)
    {
        if (!$value) {
            return [];
        }

        $data = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TransformationFailedException('JSON invalide pour les réponses.');
        }

        return $data;
    }
}
