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
        // /* Get unit */
        // $unitId = $this->getUnitId();

        $filter
            ->add('name')
        ;

        // /* If unit is not selected, unit selection is available */
        // if (!$unitId) {
        //     $filter
        //         ->add('unit', null, [
        //             'label' => 'Unit',
        //             'show_filter' => true,
        //             'field_type' => EntityType::class,
        //             'field_options' => [
        //                 'class' => Unit::class,
        //                 'choice_label' => 'name',
        //                 'query_builder' => function (EntityRepository $er) {
        //                     return $er->createQueryBuilder('u')
        //                         ->andWhere('u.active = :active')
        //                         ->orderBy('u.name', 'ASC')
        //                         ->setParameter('active', true)
        //                     ;
        //                 },
        //             ],
        //         ])
        //     ;
        // }
    }

    // MARK: - List Fields
    protected function configureListFields(ListMapper $list): void
    {
        // /* Get unit */
        // $unitId = $this->getUnitId();

        $actions = [
            'show' => [
                'template' => 'CRUD/list__action_show_custom.html.twig',
            ],
            'edit' => [
                'template' => 'CRUD/list__action_edit_custom.html.twig',
            ],
            'delete' => [
                'template' => 'CRUD/list__action_delete_custom.html.twig',
            ],
        ];

        $list
            // ->add($unitId ? 'name' : 'nameWithUnit', null, [
            //     'label' => $unitId ? 'Name' : 'Name (Unit)'
            // ])
            ->add('name')
            ->add('unit')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'header_style' => 'width: 25%;',
                'actions' => $actions,
                    // 'subCategories' => [
                    //     'template' => 'Budget/list__action_sub_categories_show.html.twig',
                    // ],
            ])
        ;

        // if (!$unitId) {
        //     $list
        //         ->add('unit.name', null, [
        //             'label' => 'Unit'
        //         ])
        //     ;
        // }
    }

    // MARK: - Form Fields
    protected function configureFormFields(FormMapper $form): void
    {
        // /* Get unit */
        // $unitId = $this->getUnitId();

        $form
            ->with('Budget Main Category', ['class' => 'col-md-6'])
                    ->add('name')
            ->end()
        ;

        // if (!$unitId) {
        //     $form
        //         ->add('unit', EntityType::class, [
        //             'label' => 'Unit',
        //             'class' => Unit::class,
        //             'choice_label' => 'name',
        //             'placeholder' => 'Choose an option',
        //             'query_builder' => function (EntityRepository $er) {
        //                 return $er->createQueryBuilder('u')
        //                     ->andWhere('u.active = :active')
        //                     ->orderBy('u.name', 'ASC')
        //                     ->setParameter('active', true)
        //                 ;
        //             },
        //         ])
        //     ;
        // }
    }

    // MARK: - Show Fields
    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Budget Main Category', ['class' => 'col-md-6'])
                ->add('name')
                ->add('unit.name', null, [
                    'label' => 'Unit'
                ])
            ->end()
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
        //     $object->setUnit($unit);
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
        //         ->join($rootAlias . '.unit', 'u')
        //         ->andWhere('u.active = :active')
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
