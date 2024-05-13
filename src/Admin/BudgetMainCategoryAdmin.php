<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\BudgetMainCategory;
use App\Entity\InvoiceLine;
use App\Entity\PurchaseLine;
use App\Repository\InvoiceLineRepository;
use App\Repository\PurchaseLineRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

final class BudgetMainCategoryAdmin extends AbstractAdmin
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /* Remove batch delete action from the list */
    protected function configureBatchActions($actions): array
    {
        unset($actions['delete']);

        return $actions;
    }

    // /* Remove Download button from bottom of the list */
    // public function getExportFormats(): array
    // {
    //     return [];
    // }

    // MARK: - Datagrid Filters
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
        ;
    }

    // MARK: - List Fields
    protected function configureListFields(ListMapper $list): void
    {
        // /* Get unit */
        // $unitId = $this->getUnitId();

        $list
            // ->add($unitId ? 'name' : 'nameWithUnit', null, [
            //     'label' => $unitId ? 'Name' : 'Name (Unit)'
            // ])
            ->add('name')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [
                        // 'template' => 'CRUD/list__action_edit_no_label.html.twig',
                    ],
                    'delete' => [],
                    // 'subCategories' => [
                    //     'template' => 'Budget/list__action_sub_categories_show.html.twig',
                    // ],
                ],
            ])
        ;
    }

    // MARK: - Form Fields
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Budget Sub Category', ['class' => 'col-md-6'])
                    ->add('name')
            ->end()
        ;
    }

    // MARK: - Show Fields
    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('name')
        ;
    }

    // MARK: - PrePersist
    protected function prePersist(object $object): void
    {
        /** @var BudgetMainCategory $object */

        // /** @var UnitRepository $unitRepository */
        // $unitRepository = $this->entityManager->getRepository(Unit::class);

        // /* Get unit */
        // $unitId = $this->getUnitId();

        // if ($unitId) {
        //     $unit = $unitRepository->findOneBy(['id' => $unitId]);
        //     $account->setUnit($unit);
        // }
    }

    // MARK: - PreRemove
    // protected function preRemove(object $object): void
    // {
    //     /** @var InvoiceLineRepository $invoiceLineRepository */
    //     $invoiceLineRepository = $this->entityManager->getRepository(InvoiceLine::class);
    //     /** @var PurchaseLineRepository $purchaseLineRepository */
    //     $purchaseLineRepository =  $this->entityManager->getRepository(PurchaseLine::class);

    //     /* MAIN CATEGORIES LINES REMOVE */
    //     $invoiceLinesMain = $object->getInvoiceLines()->toArray();
    //     $purchaseLinesMain = $object->getPurchaseLines()->toArray();

    //     if ($invoiceLinesMain) {
    //         foreach ($invoiceLinesMain as $line) {
    //             $line->setBudgetMainCategory(null);

    //             $invoiceLineRepository->add($line, true);
    //         }
    //     }

    //     if ($purchaseLinesMain) {
    //         foreach ($purchaseLinesMain as $line) {
    //             $line->setBudgetMainCategory(null);

    //             $purchaseLineRepository->add($line, true);
    //         }
    //     }

    //     $budgetSubCategories = $object->getBudgetSubCategories()->toArray();

    //     /* SUB CATEGORIES LINES REMOVE */
    //     if ($budgetSubCategories) {
    //         foreach ($budgetSubCategories as $category) {
    //             $invoiceLinesSub = $category->getInvoiceLines()->toArray();
    //             $purchaseLinesSub = $category->getPurchaseLines()->toArray();

    //             if ($invoiceLinesSub) {
    //                 foreach ($invoiceLinesSub as $line) {
    //                     $line->setBudgetCategory(null);

    //                     $invoiceLineRepository->add($line, true);
    //                 }
    //             }

    //             if ($purchaseLinesSub) {
    //                 foreach ($purchaseLinesSub as $line) {
    //                     $line->setBudgetCategory(null);

    //                     $purchaseLineRepository->add($line, true);
    //                 }
    //             }
    //         }
    //     }
    // }

    // MARK: - Configure Query
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        if (!$query instanceof ProxyQuery) {
            throw new InvalidArgumentException('Expected an instance of ProxyQuery');
        }

        // $qb = $query->getQueryBuilder();
        // $rootAlias = current($query->getQueryBuilder()->getRootAliases());

        // /* Get unit */
        // $unitId = $this->getUnitId();

        // if ($unitId) {
        //     $qb
        //         ->where($rootAlias . '.unit = :unitId')
        //         ->setParameter('unitId', $unitId)
        //     ;
        // } else {
        //     $qb
        //         ->join($rootAlias . '.unit', 's')
        //         ->andWhere('s.active = :active')
        //         ->setParameter('active', true)
        //     ;
        // }

        return $query;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
        $sortValues[DatagridInterface::SORT_BY] = 'name';
    }
}
