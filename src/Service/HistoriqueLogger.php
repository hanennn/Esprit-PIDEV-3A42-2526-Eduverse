<?php

namespace App\Service;

use App\Entity\Historique;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class HistoriqueLogger
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function log(string $entityType, string $action, ?int $entityId, string $description, ?User $actor): void
    {
        $historique = new Historique();
        $historique
            ->setEntityType($entityType)
            ->setAction($action)
            ->setEntityId($entityId)
            ->setDescription($description)
            ->setActor($actor)
            ->setActorIdentifier($this->resolveActorIdentifier($actor));

        $this->entityManager->persist($historique);
    }

    private function resolveActorIdentifier(?User $actor): ?string
    {
        if (!$actor) {
            return null;
        }

        $fullName = trim(sprintf('%s %s', (string) $actor->getPrenom(), (string) $actor->getNom()));

        if ($fullName !== '') {
            return $fullName;
        }

        return $actor->getUserIdentifier();
    }
}
