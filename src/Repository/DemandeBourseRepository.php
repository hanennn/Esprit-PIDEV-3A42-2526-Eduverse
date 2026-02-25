<?php

namespace App\Repository;

use App\Entity\DemandeBourse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandeBourse>
<<<<<<< HEAD
 *
 * @method DemandeBourse|null find($id, $lockMode = null, $lockVersion = null)
 * @method DemandeBourse|null findOneBy(array $criteria, array $orderBy = null)
 * @method DemandeBourse[]    findAll()
 * @method DemandeBourse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
=======
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
 */
class DemandeBourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeBourse::class);
    }

<<<<<<< HEAD
    public function save(DemandeBourse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DemandeBourse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
=======
    //    /**
    //     * @return DemandeBourse[] Returns an array of DemandeBourse objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DemandeBourse
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
>>>>>>> ee09f695887cdbc96e92b8b02f40161029db34ed
}
