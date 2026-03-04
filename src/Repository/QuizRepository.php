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
  /**
 * @param array{title?: string, type?: string, sort?: string, order?: 'ASC'|'DESC'} $filters
 * @return array<int, Quiz>
 */
public function searchByCourse(Cours $course, array $filters): array
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
        $qb->orderBy('q.' . $filters['sort'], $filters['order'] ?? 'ASC');
    }

    /** @var array<int, Quiz> $result */
    $result = $qb->getQuery()->getResult();

    return $result;
}
/**
 * @return array{totalQuizzes: string|int, totalCertifications: string|int}
 */
public function getGlobalStats(): array
    {
        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.certifications', 'c') 
            ->select('COUNT(DISTINCT q.id) as totalQuizzes')
            ->addSelect('COUNT(c.id) as totalCertifications');
         /** @var array{totalQuizzes: string|int, totalCertifications: string|int} $row */
        $row = $qb->getQuery()->getSingleResult();
       return $row;
    }
    /**
 * @return array<int, array{0: Quiz, certCount: string|int}>
 */

    
    public function getDifficultyRanking(): array
    {
        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.certifications', 'c')
            ->addSelect('q', 'COUNT(c.id) as certCount')
            ->groupBy('q.id')
            ->orderBy('certCount', 'DESC');

        /** @var array<int, array{0: Quiz, certCount: string|int}> $rows */
    $rows = $qb->getQuery()->getResult();
    return $rows;
    }

/**
 * @return array<int, Quiz>
 */


    public function findByInstructor(User $instructor): array
    {
        /** @var array<int, Quiz> $rows */
    $rows = $this->createQueryBuilder('q')
        ->innerJoin('q.coursAssocie', 'c')
        ->where('c.createur = :instructor')
        ->setParameter('instructor', $instructor)
        ->orderBy('q.id', 'DESC')
        ->getQuery()
        ->getResult();

    return $rows;
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