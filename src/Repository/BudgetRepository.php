<?php

namespace App\Repository;

use App\Entity\Budget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Budget>
 *
 * @method Budget|null find($id, $lockMode = null, $lockVersion = null)
 * @method Budget|null findOneBy(array $criteria, array $orderBy = null)
 * @method Budget[]    findAll()
 * @method Budget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Budget::class);
    }

    // /**
    //  * @throws Exception
    //  */
    // public function findWithTotals(string $id): ?Budget
    // {
    //     $budget = $this->find($id);

    //     if ($budget) {
    //         $entityManager = $this->getEntityManager();

    //         try {
    //             $query = $entityManager->createQueryBuilder()
    //                 ->select('
    //                     SUM(bi.budgeted) as totalBudgeted,
    //                     SUM(bi.actualAvg) as totalActual,
    //                     bi.currency
    //                 ')
    //                 ->from(BudgetItem::class, 'bi')
    //                 ->where('bi.budget = :budget')
    //                 ->groupBy('bi.currency')
    //                 ->orderBy('bi.currency')
    //                 ->setParameter('budget', $budget)
    //                 ->getQuery()
    //             ;

    //             $results = $query->getResult();

    //             foreach ($results as $result) {
    //                 $budget->setTotalBudgeted($result['currency'], $result['totalBudgeted']);
    //                 $budget->setTotalActual($result['currency'], $result['totalActual']);
    //             }
    //         } catch (Exception $e) {
    //             error_log($e->getMessage());

    //             return null;
    //         }
    //     }

    //     return $budget;
    // }

    // /**
    //  * @throws Exception
    //  */
    // public function findBudgetInvoices(string $objectId): array
    // {
    //     /** @var InvoiceRepository $invoiceRepository */
    //     $invoiceRepository = $this->getEntityManager()->getRepository(Invoice::class);
    //     /** @var BudgetItemRepository $budgetItemRepository */
    //     $budgetItemRepository = $this->getEntityManager()->getRepository(BudgetItem::class);

    //     $budget = $this->findOneBy(['id' => $objectId]);

    //     try {
    //         $budgetItems = $budgetItemRepository->createQueryBuilder('bi')
    //             ->where('bi.budget = :budget')
    //             ->setParameter('budget', $budget)
    //             ->getQuery()
    //             ->getResult()
    //         ;

    //         $budgetCurrencies = $this->getUniqueValues($budgetItems, 'getCurrency');
    //         $budgetMainCategories = $this->getUniqueValues($budgetItems, 'getBudgetMainCategory');
    //         $budgetCategories = $this->getUniqueValues($budgetItems, 'getBudgetCategory');

    //         $budgetInvoices = $invoiceRepository->createQueryBuilder('i')
    //             ->join('i.invoiceLines', 'il')
    //             ->where('i.invoicePaymentStatus = :paid')
    //             ->andWhere('i.budget = :budget')
    //             ->andWhere('i.currency IN (:budgetCurrencies)')
    //             ->andWhere('il.budgetMainCategory IN (:budgetMainCategories)')
    //             ->andWhere('il.budgetCategory IN (:budgetCategories)')
    //             ->setParameter('paid', 'Paid')
    //             ->setParameter('budget', $budget)
    //             ->setParameter('budgetCurrencies', $budgetCurrencies)
    //             ->setParameter('budgetMainCategories', $budgetMainCategories)
    //             ->setParameter('budgetCategories', $budgetCategories)
    //             ->orderBy('i.invoiceDate', 'ASC')
    //             ->getQuery()
    //             ->getResult()
    //         ;

    //         return $budgetInvoices;
    //     } catch (Exception $e) {
    //         error_log($e->getMessage());

    //         return [];
    //     }
    // }

    // /**
    //  * @throws Exception
    //  */
    // public function findBudgetPurchases(string $objectId): array
    // {
    //     /** @var PurchaseRepository $purchaseRepository */
    //     $purchaseRepository = $this->getEntityManager()->getRepository(Purchase::class);
    //     /** @var BudgetItemRepository $budgetItemRepository */
    //     $budgetItemRepository = $this->getEntityManager()->getRepository(BudgetItem::class);

    //     $budget = $this->findOneBy(['id' => $objectId]);

    //     try {
    //         $budgetItems = $budgetItemRepository->createQueryBuilder('bi')
    //             ->where('bi.budget = :budget')
    //             ->setParameter('budget', $budget)
    //             ->getQuery()
    //             ->getResult()
    //         ;

    //         $budgetCurrencies = $this->getUniqueValues($budgetItems, 'getCurrency');
    //         $budgetMainCategories = $this->getUniqueValues($budgetItems, 'getBudgetMainCategory');
    //         $budgetCategories = $this->getUniqueValues($budgetItems, 'getBudgetCategory');

    //         $budgetPurchases = $purchaseRepository->createQueryBuilder('p')
    //             ->join('p.purchaseLines', 'pl')
    //             ->where('p.budget = :budget')
    //             ->andWhere('p.currency IN (:budgetCurrencies)')
    //             ->andWhere('pl.budgetMainCategory IN (:budgetMainCategories)')
    //             ->andWhere('pl.budgetCategory IN (:budgetCategories)')
    //             ->setParameter('budget', $budget)
    //             ->setParameter('budgetCurrencies', $budgetCurrencies)
    //             ->setParameter('budgetMainCategories', $budgetMainCategories)
    //             ->setParameter('budgetCategories', $budgetCategories)
    //             ->orderBy('p.dateOfPurchase', 'ASC')
    //             ->getQuery()
    //             ->getResult()
    //         ;

    //         return $budgetPurchases;
    //     } catch (Exception $e) {
    //         error_log($e->getMessage());

    //         return [];
    //     }
    // }

    // private function getUniqueValues(array $items, string $method): array {
    //     return array_values(array_unique(array_map(function($item) use ($method) {
    //         return $item->$method();
    //     }, $items)));
    // }

    //    /**
    //     * @return Budget[] Returns an array of Budget objects
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

    //    public function findOneBySomeField($value): ?Budget
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
