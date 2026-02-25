<?php

namespace App\Repository;

use App\Entity\Bourse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bourse>
<<<<<<< HEAD
 *
 * @method Bourse|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bourse|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bourse[]    findAll()
 * @method Bourse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
=======
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
 */
class BourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bourse::class);
    }
<<<<<<< HEAD
=======

    //    /**
    //     * @return Bourse[] Returns an array of Bourse objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Bourse
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
}
