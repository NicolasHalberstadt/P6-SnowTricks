<?php

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trick::class);
    }
    
    /**
     * @return Trick[] Returns an array of Trick objects
     */
    public function findHomepageTricks(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(15)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * @param $lastId
     * @return Trick[] Returns an array of Trick objects
     */
    public function loadMoreTricks(int $lastId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.id > :lastId')
            ->setParameter('lastId', $lastId)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(15)
            ->getQuery()
            ->getResult();
    }

}
