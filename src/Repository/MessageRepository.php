<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Find all messages for a specific sujet ordered by date
     * @return Message[]
     */
    public function findBySujetOrderByDate(int $sujetId): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.sujet = :sujet_id')
            ->setParameter('sujet_id', $sujetId)
            ->orderBy('m.datePublication', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
