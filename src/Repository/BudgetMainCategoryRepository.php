<?php

namespace App\Repository;

use App\Entity\BudgetMainCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BudgetMainCategory>
 *
 * @method BudgetMainCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method BudgetMainCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method BudgetMainCategory[]    findAll()
 * @method BudgetMainCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BudgetMainCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetMainCategory::class);
    }

    //    /**
    //     * @return BudgetMainCategory[] Returns an array of BudgetMainCategory objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?BudgetMainCategory
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
