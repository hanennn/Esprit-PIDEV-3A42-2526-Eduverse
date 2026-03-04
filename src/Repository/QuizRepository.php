<?php

namespace App\Repository;

use App\Entity\Cours;
use App\Entity\User;
use App\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quiz>
 */
class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }
   public function searchByCourse(Cours $course, array $filters)
{
    $qb = $this->createQueryBuilder('q')
        ->where('q.coursAssocie = :course')
        ->setParameter('course', $course);

    if (!empty($filters['title'])) {
        $qb->andWhere('q.titre LIKE :title')
           ->setParameter('title', '%' . $filters['title'] . '%');
    }

    if (!empty($filters['type'])) {
        $qb->andWhere('q.typeQuiz = :type')
           ->setParameter('type', $filters['type']);
    }

    if (!empty($filters['sort'])) {
        $qb->orderBy('q.' . $filters['sort'], $filters['order']);
    }

    return $qb->getQuery()->getResult();
}
public function getGlobalStats(): array
    {
        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.certifications', 'c') 
            ->select('COUNT(DISTINCT q.id) as totalQuizzes')
            ->addSelect('COUNT(c.id) as totalCertifications');

        return $qb->getQuery()->getSingleResult();
    }

    
    public function getDifficultyRanking(): array
    {
        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.certifications', 'c')
            ->addSelect('q', 'COUNT(c.id) as certCount')
            ->groupBy('q.id')
            ->orderBy('certCount', 'DESC');

        return $qb->getQuery()->getResult();
    }


    public function findByInstructor(User $instructor): array
    {
        return $this->createQueryBuilder('q')
            ->innerJoin('q.coursAssocie', 'c')
            ->where('c.createur = :instructor')
            ->setParameter('instructor', $instructor)
            ->orderBy('q.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
    

    //    /**
    //     * @return Quiz[] Returns an array of Quiz objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Quiz
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}