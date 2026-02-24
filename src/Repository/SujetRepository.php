<?php

namespace App\Repository;

use App\Entity\Sujet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sujet>
 */
class SujetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sujet::class);
    }

    /**
     * Find all sujets ordered by date creation (newest first)
     * @return Sujet[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search sujets by titre or contenu
     * @return Sujet[]
     */
    public function searchBySujet(string $searchTerm): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.auteur', 'u')
            ->where('s.titre LIKE :search OR s.contenu LIKE :search OR u.prenom LIKE :search OR u.nom LIKE :search')
            ->setParameter('search', '%' . $searchTerm . '%')
            ->orderBy('s.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
