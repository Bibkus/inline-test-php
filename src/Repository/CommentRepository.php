<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }
    public function findBySubstring(string $substring): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT p.id AS post_id, p.title AS post_title, c.body AS comment_body
     FROM App\Entity\Comment c
     JOIN c.post p
     WHERE c.body LIKE :substring'
        )->setParameter('substring', "%$substring%");

        $results = $query->getResult();
        $groupedResults = [];
        foreach ($results as $result) {
            $groupedResults[$result['post_id']]['post_title'] = $result['post_title'];
            $groupedResults[$result['post_id']]['comments'][] = $result['comment_body'];
        }

        return $groupedResults;
    }
    //    /**
    //     * @return Comment[] Returns an array of Comment objects
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

    //    public function findOneBySomeField($value): ?Comment
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
