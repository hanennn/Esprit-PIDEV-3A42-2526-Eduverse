<?php

namespace App\Repository;

use App\Entity\DemandeBourse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandeBourse>
 *
 * @method DemandeBourse|null find($id, $lockMode = null, $lockVersion = null)
 * @method DemandeBourse|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method DemandeBourse[]    findAll()
 * @method DemandeBourse[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 */
class DemandeBourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeBourse::class);
    }

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
}
