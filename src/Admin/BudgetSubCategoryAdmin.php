<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\BudgetMainCategory;
use App\Entity\BudgetCategory;
use App\Entity\BudgetSubCategory;
use App\Entity\InvoiceLine;
use App\Entity\PurchaseLine;
use App\Repository\InvoiceLineRepository;
use App\Repository\PurchaseLineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

final class BudgetSubCategoryAdmin extends AbstractAdmin
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
            ->add('budgetMainCategory', null, [
                'label' => 'Main Category',
                'show_filter' => true,
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => BudgetMainCategory::class,
                    'choice_label' => 'name',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('bmc')
                            ->orderBy('bmc.name', 'ASC');
                    },
                ],
            ])
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

        /* TODO: add budget expenses per category / main category*/
        $list
            ->add('name')
            ->add('budgetMainCategory.name', null, [
                'label' => 'Main Category'
            ])
            ->add('unit')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'header_style' => 'width: 25%;',
                'actions' => $actions,
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
            ->with('Budget Sub Category', ['class' => 'col-md-6'])
                ->add('name')
                ->add('budgetMainCategory', EntityType::class, [
                    'class' => BudgetMainCategory::class,
                    'choice_label' => 'name',
                    // 'choice_label' => $unitId ? 'name' : 'nameWithUnit',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('bmc')
                            ->orderBy('bmc.name', 'ASC');
                    },
                ])
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
            ->with('Budget Sub Category', ['class' => 'col-md-6'])
                ->add('name')
                ->add('budgetMainCategory.name', null, [
                    'label' => 'Main Category'
                ])
                ->add('unit.name', null, [
                    'label' => 'Unit'
                ])
            ->end()
        ;
    }

    // MARK: - PrePersist
    protected function prePersist(object $object): void
    {
        /** @var BudgetSubCategory $object */

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
    //     /** @var BudgetSubCategory $object */

    //     /** @var InvoiceLineRepository $invoiceLineRepository */
    //     $invoiceLineRepository = $this->entityManager->getRepository(InvoiceLine::class);
    //     /** @var PurchaseLineRepository $purchaseLineRepository */
    //     $purchaseLineRepository = $this->entityManager->getRepository(PurchaseLine::class);

    //     $invoiceLines = $object->getInvoiceLines()->toArray();
    //     $purchaseLines = $object->getPurchaseLines()->toArray();

    //     if ($invoiceLines) {
    //         foreach ($invoiceLines as $line) {
    //             $line->setBudgetCategory(null);

    //             $invoiceLineRepository->add($line, true);
    //         }
    //     }

    //     if ($purchaseLines) {
    //         foreach ($purchaseLines as $line) {
    //             $line->setBudgetCategory(null);

    //             $purchaseLineRepository->add($line, true);
    //         }
    //     }
    // }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
        $sortValues[DatagridInterface::SORT_BY] = 'name';
    }
}
