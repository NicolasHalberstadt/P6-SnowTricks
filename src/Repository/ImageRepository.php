<?php

namespace App\Repository;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
    }
    
    /**
     * @param string $name
     * @param int $trickId
     * @return Image[] Returns an array of Image objects
     */
    public function findOneByName(string $name, int $trickId): array
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.name LIKE :name')
            ->andWhere('i.trick = :trick_id')
            ->setParameters([
                'name' => '%' . $name . '%',
                'trick_id' => $trickId
            ]);
        $query = $qb->getQuery();
        return $query->execute();
    }
    
    
    /**
     * @param int $trickId
     * @return Image[] Returns an array of Image objects
     */
    public function findMainPic(int $trickId): array
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.trick = :trick_id')
            ->andWhere('i.isMain = true')
            ->setParameter('trick_id', $trickId);
        $query = $qb->getQuery();
        return $query->execute();
    }
}
