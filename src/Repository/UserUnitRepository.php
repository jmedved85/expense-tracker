<?php

namespace App\Repository;

use App\Entity\UserUnit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserUnit>
 *
 * @method UserUnit|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserUnit|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserUnit[]    findAll()
 * @method UserUnit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserUnitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserUnit::class);
    }

    //    /**
    //     * @return UserUnit[] Returns an array of UserUnit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?UserUnit
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
