<?php

namespace App\Repository;

use App\Entity\BudgetItem;
use App\Entity\Invoice;
use App\Entity\Purchase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @extends ServiceEntityRepository<BudgetItem>
 *
 * @method BudgetItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method BudgetItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method BudgetItem[]    findAll()
 * @method BudgetItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BudgetItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetItem::class);
    }

    /**
     * @throws Exception
     */
    public function findBudgetItemInvoices(string $objectId): array
    {
        /** @var InvoiceRepository $invoiceRepository */
        $invoiceRepository = $this->getEntityManager()->getRepository(Invoice::class);

        $budgetItem = $this->findOneBy(['id' => $objectId]);

        $budget = $budgetItem->getBudget();
        $budgetSubCategory = $budgetItem->getBudgetSubCategory();
        $budgetCurrency = $budgetItem->getCurrency();

        try {
            $budgetItemInvoices = $invoiceRepository->createQueryBuilder('i')
                ->join('i.invoiceLines', 'il')
                ->where('i.invoicePaymentStatus = :paid')
                ->andWhere('i.currency = :currency')
                ->andWhere('i.budget = :budget')
                ->andWhere('il.budgetMainCategory = :budgetMainCategory')
                ->andWhere('il.budgetCategory = :budgetCategory')
                ->setParameter('paid', 'Paid')
                ->setParameter('currency', $budgetCurrency)
                ->setParameter('budget', $budget->getId())
                ->setParameter('budgetSubCategory', $budgetSubCategory->getId())
                ->orderBy('i.invoiceDate', 'ASC')
                ->getQuery()
                ->getResult()
            ;

            return $budgetItemInvoices;
        } catch (Exception $e) {
            error_log($e->getMessage());

            return [];
        }
    }

    /**
     * @throws Exception
     */
    public function findBudgetItemPurchases(string $objectId): array
    {
        /** @var PurchaseRepository $purchaseRepository */
        $purchaseRepository = $this->getEntityManager()->getRepository(Purchase::class);

        $budgetItem = $this->findOneBy(['id' => $objectId]);

        $budget = $budgetItem->getBudget();
        $budgetSubCategory = $budgetItem->getBudgetSubCategory();
        $budgetCurrency = $budgetItem->getCurrency();

        try {
            $budgetItemPurchases = $purchaseRepository->createQueryBuilder('p')
                ->join('p.purchaseLines', 'pl')
                ->where('p.budget = :budget')
                ->andWhere('p.currency = :currency')
                ->andWhere('pl.budgetSubCategory = :budgetSubCategory')
                ->setParameter('currency', $budgetCurrency)
                ->setParameter('budget', $budget->getId())
                ->setParameter('budgetSubCategory', $budgetSubCategory->getId())
                ->orderBy('p.dateOfPurchase', 'ASC')
                ->getQuery()
                ->getResult()
            ;

            return $budgetItemPurchases;
        } catch (Exception $e) {
            error_log($e->getMessage());

            return [];
        }
    }

//    /**
//     * @return BudgetItem[] Returns an array of BudgetItem objects
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

//    public function findOneBySomeField($value): ?BudgetItem
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
