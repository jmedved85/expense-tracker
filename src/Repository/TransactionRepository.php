<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Transaction;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function add(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getTransferFromAccount(string $id): ?Account
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->getEntityManager()->getRepository(Account::class);

        $accountId = $this->createQueryBuilder('t')
            ->select('identity(t.transferFromAccount)')
            ->andWhere('t.id = :transactionId')
            ->setParameter('transactionId', $id)
            ->getQuery()
            ->getResult();

        return $accountRepository->getAccount($accountId[0][1]);
    }

    public function getTransferToAccount(string $id): ?Account
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->getEntityManager()->getRepository(Account::class);

        $accountId = $this->createQueryBuilder('t')
            ->select('identity(t.transferToAccount)')
            ->andWhere('t.id = :transactionId')
            ->setParameter('transactionId', $id)
            ->getQuery()
            ->getResult();

        return $accountRepository->getAccount($accountId[0][1]);
    }

    public function getOldDate(string $id): ?DateTime
    {
        $oldDateQuery = $this->createQueryBuilder('t')
            ->select('t.date')
            ->where('t.id = :transactionId')
            ->setParameter('transactionId', $id)
            ->getQuery()
            ->getResult();

        return $oldDateQuery[0]['date'];
    }

    public function getOldAmountFromAccount(string $id): ?string
    {
        $oldAmountFromAccountQuery = $this->createQueryBuilder('t')
            ->select('t.amountFromAccount')
            ->where('t.id = :transactionId')
            ->setParameter('transactionId', $id)
            ->getQuery()
            ->getResult();

        return $oldAmountFromAccountQuery[0]['amountFromAccount'];
    }

    public function getOldAmount(string $id): ?string
    {
        $oldAmountQuery = $this->createQueryBuilder('t')
            ->select('t.amount')
            ->where('t.id = :transactionId')
            ->setParameter('transactionId', $id)
            ->getQuery()
            ->getResult();

        return $oldAmountQuery[0]['amount'];
    }

    public function getOldCurrentNewValue(string $id): ?string
    {
        $currentNewValueQuery = $this->createQueryBuilder('t')
            ->select('t.newValue')
            ->where('t.id = :transactionId')
            ->setParameter('transactionId', $id)
            ->getQuery()
            ->getResult();

        return $currentNewValueQuery[0]['newValue'];
    }

    public function getOldTransferToAccount(string $id): ?Account
    {
        $query = $this->createQueryBuilder('t')
            ->select('a.id')
            ->where('t.id = :transactionId')
            ->join('t.transferToAccount', 'a')
            ->setParameter('transactionId', $id)
            ->getQuery()
            ->getResult();

        $oldTransferToAccountId = $query[0]['id'];

        return $this->getEntityManager()->getRepository(Account::class)->findOneBy(['id' => $oldTransferToAccountId]);
    }

//    /**
//     * @return Transaction[] Returns an array of Transaction objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Transaction
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
