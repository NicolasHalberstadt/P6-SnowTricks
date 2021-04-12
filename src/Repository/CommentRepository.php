<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }
    
    /**
     * @param int $trickId
     * @return array
     */
    public function getComments(int $trickId): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.id', 'DESC')
            ->andWhere('c.trick = :trickId')
            ->setParameter('trickId', $trickId)
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * @param int $offset
     * @param int $trickId
     * @return array
     */
    public function loadMoreComments(int $offset, int $trickId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.trick = :trickId')
            ->setParameter('trickId', $trickId)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(3)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * @param int $trickId
     * @return int
     */
    public function countComments(int $trickId): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->andWhere('c.trick = :trickId')
            ->setParameter('trickId', $trickId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
