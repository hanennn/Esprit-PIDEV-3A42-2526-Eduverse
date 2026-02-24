<?php

namespace App\Repository;

use Doctrine\ORM\Query;

use App\Entity\Cours;
use App\Entity\user;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cours>
 */
class CoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cours::class);
    }

    //    /**
    //     * @return Cours[] Returns an array of Cours objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Cours
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function searchAndSort(?string $search, ?string $criteria, ?string $sort): array
{
    $qb = $this->createQueryBuilder('c');

    $map = [
        'titre'   => 'c.titre_cours',
        'niv'     => 'c.niv_cours',
        'matiere' => 'c.matiere_cours',
        'langue'  => 'c.langue_cours',
    ];

    // 🔍 recherche
    if (!empty($search) && isset($map[$criteria])) {
        $qb->andWhere($map[$criteria] . ' LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }

    // 🔃 tri
    if (isset($map[$sort])) {
        $qb->orderBy($map[$sort], 'ASC');
    } else {
        $qb->orderBy('c.id', 'DESC');
    }

    return $qb->getQuery()->getResult();
}

   
public function searchAndSortBack(?string $search = null, ?string $criteria = null, ?string $sort = null): array
    {
        $qb = $this->createQueryBuilder('c');

        // Apply search filter
        if (!empty($search) && !empty($criteria)) {
            if ($criteria === 'titre') {
                $qb->andWhere('c.titre_cours LIKE :search');
                $qb->setParameter('search', '%' . $search . '%');
            } elseif ($criteria === 'niv') {
                $qb->andWhere('c.niv_cours LIKE :search');
                $qb->setParameter('search', '%' . $search . '%');
            } elseif ($criteria === 'matiere') {
                $qb->andWhere('c.matiere_cours LIKE :search');
                $qb->setParameter('search', '%' . $search . '%');
            } elseif ($criteria === 'langue') {
                $qb->andWhere('c.langue_cours LIKE :search');
                $qb->setParameter('search', '%' . $search . '%');
            }
        }

        // Apply sorting - only use valid fields
        if ($sort === 'titre') {
            $qb->orderBy('c.titre_cours', 'ASC');
        } elseif ($sort === 'niv') {
            $qb->orderBy('c.niv_cours', 'ASC');
        } elseif ($sort === 'matiere') {
            $qb->orderBy('c.matiere_cours', 'ASC');
        } elseif ($sort === 'langue') {
            $qb->orderBy('c.langue_cours', 'ASC');
        } else {
            // Default sort by most recent
            $qb->orderBy('c.id', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
}