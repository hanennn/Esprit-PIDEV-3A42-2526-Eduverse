<?php

namespace App\Repository;

use App\Entity\EventInscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventInscription>
 *
 * @method EventInscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventInscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventInscription[]    findAll()
 * @method EventInscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventInscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventInscription::class);
    }
}
