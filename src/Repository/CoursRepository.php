<?php

namespace App\Repository;

use App\Entity\Cours;
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

    // 🔍 Recherche multicritère
    if (!empty($search)) {
        if ($criteria === 'titre') {
            $qb->andWhere('c.titre_cours LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        } elseif ($criteria === 'niveau') {
            $qb->andWhere('c.niv_cours LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }elseif ($criteria === 'matiere') {
            $qb->andWhere('c.matiere_cours LIKE :search')
               ->setParameter('search', '%' . $search . '%');
    }
    }

    //  Tri
    if ($sort === 'titre') {
        $qb->orderBy('c.titre_cours', 'ASC');
    } elseif ($sort === 'niveau') {
        $qb->orderBy('c.niv_cours', 'ASC');
    } else {
        $qb->orderBy('c.id', 'DESC'); // tri par défaut
    }

    return $qb->getQuery()->getResult();
}
// src/Repository/ChapitreRepository.php

public function searchAndSortBack(?string $search, ?string $criteria, ?string $sort): array
{
    $qb = $this->createQueryBuilder('c');

    if (!empty($search)) {
        switch ($criteria) {
            case 'titre':
                $qb->andWhere('c.titre_cours LIKE :search');
                break;
            case 'niv':
                $qb->andWhere('c.niv_cours LIKE :search');
                break;
            case 'matiere':
                $qb->andWhere('c.matiere_cours LIKE :search');
                break;
            case 'langue':
                $qb->andWhere('c.langue_cours LIKE :search');
                break;
        }
        $qb->setParameter('search', '%' . $search . '%');
    }

    switch ($sort) {
        case 'titre':
            $qb->orderBy('c.titre_cours', 'ASC');
            break;
        case 'niv':
            $qb->orderBy('c.niv_cours', 'ASC');
            break;
        case 'matiere':
            $qb->orderBy('c.matiere_cours', 'ASC');
            break;
        case 'langue':
            $qb->orderBy('c.langue_cours', 'ASC');
            break;
        default:
            $qb->orderBy('c.id', 'DESC');
    }

    return $qb->getQuery()->getResult();
}


}