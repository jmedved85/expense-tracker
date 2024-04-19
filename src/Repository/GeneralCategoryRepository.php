<?php

namespace App\Repository;

use App\Entity\GeneralCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GeneralCategory>
 *
 * @method GeneralCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeneralCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeneralCategory[]    findAll()
 * @method GeneralCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeneralCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeneralCategory::class);
    }

    //    /**
    //     * @return GeneralCategory[] Returns an array of GeneralCategory objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?GeneralCategory
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
