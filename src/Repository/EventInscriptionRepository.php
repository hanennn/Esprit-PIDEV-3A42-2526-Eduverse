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

    /**
     * Finds inscriptions for a user that overlap with a given event's time range.
     *
     * @param \App\Entity\User $user
     * @param \App\Entity\Event $event
     * @return EventInscription[]
     */
    public function findOverlappingInscriptions($user, $event): array
    {
        return $this->createQueryBuilder('ei')
            ->join('ei.event', 'e')
            ->where('ei.participant = :user')
            ->andWhere('e.date = :date')
            ->andWhere('e.heureDeb < :heureFin')
            ->andWhere('e.heureFin > :heureDeb')
            ->setParameter('user', $user)
            ->setParameter('date', $event->getDate())
            ->setParameter('heureDeb', $event->getHeureDeb())
            ->setParameter('heureFin', $event->getHeureFin())
            ->getQuery()
            ->getResult();
    }
}
