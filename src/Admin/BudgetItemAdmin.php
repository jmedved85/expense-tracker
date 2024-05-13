<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Budget;
use App\Entity\BudgetItem;
use App\Entity\BudgetMainCategory;
use App\Entity\BudgetSubCategory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

final class BudgetItemAdmin extends AbstractAdmin
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function configureRoutes(RouteCollectionInterface $collection): void
    {
        // $collection
        //     ->add('viewDocument', $this->getRouterIdParameter() . '/viewDocument')
        //     ->add('sendEmail', $this->getRouterIdParameter() . '/sendEmail')
        //     ->add('emailModal', $this->getRouterIdParameter().'/emailModal')
        // ;
    }

    /* Remove batch delete action from the list */
    protected function configureBatchActions($actions): array
    {
        unset($actions['delete']);

        return $actions;
    }

    /* Remove Download button from bottom of the list */
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
            ->add('currency')
            ->add('budget', null, [
                'show_filter' => true,
                'advanced_filter' => false,
                'label' => 'Select Budget:',
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Budget::class,
                    // 'choice_label' => function(Budget $budget) use ($unitId) {
                    //     if (!$unitId) {
                    //         return $budget->getName() . ' (' . $budget->getUnit()->getName() . ')';
                    //     } else {
                    //         return $budget->getName();
                    //     }
                    // },
                    'choice_label' => 'name',
                    // 'query_builder' => function (EntityRepository $er) use ($unitId) {
                    //     if ($unitId) {
                    //         return $er->createQueryBuilder('b')
                    //             ->where('b.unit = :unit')
                    //             ->addOrderBy('b.startDate', 'ASC')
                    //             ->setParameter('unit', $unitId)
                    //         ;
                    //     } else {
                    //         return $er->createQueryBuilder('b')
                    //             ->join('b.unit', 'u')
                    //             ->where('u.active = :active')
                    //             ->addOrderBy('b.startDate', 'ASC')
                    //             ->addOrderBy('b.name', 'ASC')
                    //             ->setParameter('active', true)
                    //         ;
                    //     }
                    // }
                ],
            ])
            // ->add('budgetMainCategory', null, [
            //     'show_filter' => true,
            //     'advanced_filter' => false,
            //     'label' => 'Select Main Category:',
            //     'field_type' => EntityType::class,
            //     'field_options' => [
            //         'class' => BudgetMainCategory::class,
            //         // 'choice_label' => function(BudgetMainCategory $budgetMainCategory) use ($unitId) {
            //         //     if (!$unitId) {
            //         //         return $budgetMainCategory->getName()
                                    // . ' (' . $budgetMainCategory->getUnit()->getName() . ')';
            //         //     } else {
            //         //         return $budgetMainCategory->getName();
            //         //     }
            //         // },
            //         'choice_label' => 'name',
            //         // 'query_builder' => function (EntityRepository $er) use ($unitId) {
            //         //     if ($unitId) {
            //         //         return $er->createQueryBuilder('bmc')
            //         //             ->where('bmc.unit = :unit')
            //         //             ->addOrderBy('bmc.name', 'ASC')
            //         //             ->setParameter('unit', $unitId)
            //         //         ;
            //         //     } else {
            //         //         return $er->createQueryBuilder('bmc')
            //         //             ->join('bmc.unit', 'u')
            //         //             ->where('u.active = :active')
            //         //             ->addOrderBy('bmc.name', 'ASC')
            //         //             ->setParameter('active', true)
            //         //         ;
            //         //     }
            //         // }
            //     ],
            // ])
            ->add('budgetSubCategory', null, [
                'show_filter' => true,
                'advanced_filter' => false,
                'label' => 'Select Sub Category:',
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => BudgetSubCategory::class,
                    // 'choice_label' => function(BudgetSubCategory $budgetSubCategory) use ($unitId) {
                    //     if (!$unitId) {
                    //         return $budgetSubCategory->getName()
                                    // . ' (' . $budgetSubCategory->getUnit()->getName() . ')';
                    //     } else {
                    //         return $budgetSubCategory->getName();
                    //     }
                    // },
                    'choice_label' => 'name',
                    // 'query_builder' => function (EntityRepository $er) use ($unitId) {
                    //     if ($unitId) {
                    //         return $er->createQueryBuilder('bsc')
                    //             ->where('bsc.unit = :unit')
                    //             ->addOrderBy('bsc.name', 'ASC')
                    //             ->setParameter('unit', $unitId)
                    //         ;
                    //     } else {
                    //         return $er->createQueryBuilder('bsc')
                    //             ->join('bsc.unit', 'u')
                    //             ->where('u.active = :active')
                    //             ->addOrderBy('bsc.name', 'ASC')
                    //             ->setParameter('active', true)
                    //         ;
                    //     }
                    // }
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
        //                     return $er->createQueryBuilder('s')
        //                         ->andWhere('s.active = :active')
        //                         ->orderBy('s.name', 'ASC')
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

        $list
            ->add('budget', EntityType::class, [
                'class' => Budget::class,
                'label' => 'Budget Name',
                // 'template' => 'CRUD/list_many_to_one_no_link.html.twig'
            ])
        ;

        // if (!$unitId) {
        //     $list
        //         ->add('unit.name')
        //     ;
        // }

        $list
            // ->add('budgetMainCategory', EntityType::class, [
            //     'class' => BudgetMainCategory::class,
            //     'label' => 'Main Category',
            //     'associated_property' => 'name',
            //     // 'template' => 'CRUD/list_many_to_one_no_link.html.twig'
            // ])
            ->add('budgetSubCategory', EntityType::class, [
                'class' => BudgetSubCategory::class,
                'label' => 'Category (Main Category)',
                'associated_property' => 'name',
                // 'template' => 'CRUD/list_many_to_one_no_link.html.twig'
            ])
            ->add('currency', null, [
                'row_align' => 'center',
                'header_style' => 'text-align: center',
            ])
            ->add('budgeted', MoneyType::class, [
                // 'template' => 'CRUD/list_amount.html.twig',
                'row_align' => 'right',
                'header_style' => 'text-align: right',
            ])
            ->add('leftOver', null, [
                'label' => 'Left / Over',
                'mapped' => false,
                // 'template' => 'BudgetItem/left_over_list.html.twig',
            ])
            ->add('actual', MoneyType::class, [
                'label' => 'Actual Expenses',
                // 'template' => 'CRUD/list_amount.html.twig'
                // 'template' => 'BudgetItem/actual_expenses_amount.html.twig'
            ])
            // ->add(ListMapper::NAME_ACTIONS . '_documents', ListMapper::TYPE_ACTIONS, [
            //     'label' => 'Documents',
            //     'actions' => [
            //         'viewSendDocument' => [
            //             'template' => 'CRUD/list__action_viewSendDocument.html.twig',
            //         ],
            //     ],
            //     'row_align' => 'center',
            //     'header_style' => 'text-align: center',
            // ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [
                        // 'template' => 'CRUD/list__action_edit_no_label.html.twig',
                    ],
                    'delete' => [],
                ],
            ])
        ;
    }

    // MARK: - Form Fields
    protected function configureFormFields(FormMapper $form): void
    {
        // /* Get unit */
        // $unitId = $this->getUnitId();

        $subject = $this->getSubject();

        $form
            ->with('Budget and Categories', ['class' => 'col-md-4'])
                ->add('budget', EntityType::class, [
                    'class' => Budget::class,
                    // 'choice_label' => function(Budget $budget) use ($unitId) {
                    //     if (!$unitId) {
                    //         return $budget->getName()
                                    // . ' (' . ($budget->getUnit() ? $budget->getUnit()->getName() : null) . ')';
                    //     } else {
                    //         return $budget->getName();
                    //     }
                    // },
                    'choice_label' => 'name',
                    // 'query_builder' => function (EntityRepository $er) use ($unitId) {
                    //     if ($unitId) {
                    //         return $er->createQueryBuilder('b')
                    //             ->where('b.unit = :unit')
                    //             ->addOrderBy('b.startDate', 'ASC')
                    //             ->addOrderBy('b.name', 'ASC')
                    //             ->setParameter('unit', $unitId)
                    //         ;
                    //     } else {
                    //         return $er->createQueryBuilder('b')
                    //             ->addOrderBy('b.startDate', 'ASC')
                    //             ->addOrderBy('b.name', 'ASC')
                    //         ;
                    //     }
                    // }
                ])
                ->add('budgetSubCategory', EntityType::class, [
                    'class' => BudgetSubCategory::class,
                    'label' => 'Budget Category',
                    'placeholder' => 'Choose an option',
                    // 'choice_label' => function(BudgetSubCategory $budgetSubCategory) use ($unitId) {
                    //     if (!$unitId) {
                    //         return $budgetSubCategory->getName()
                                // . ' (' . ($budgetSubCategory->getUnit()
                                //     ? $budgetSubCategory->getUnit()->getName() : null) . ')';
                    //     } else {
                    //         return $budgetSubCategory->getName();
                    //     }
                    // },
                    'choice_label' => 'name',
                    // 'query_builder' => function (EntityRepository $er) use ($unitId) {
                    //     if ($unitId) {
                    //         return $er->createQueryBuilder('bsc')
                    //             ->where('bsc.unit = :unit')
                    //             ->addOrderBy('bsc.name', 'ASC')
                    //             ->setParameter('unit', $unitId)
                    //         ;
                    //     } else {
                    //         return $er->createQueryBuilder('bc')
                    //             ->addOrderBy('bsc.name', 'ASC')
                    //         ;
                    //     }
                    // }
                ])
            ->end()
            ->with('Currency and Amount', ['class' => 'col-md-4'])
                ->add('currency', CurrencyType::class, [
                    'placeholder' => 'Choose an option',
                    'preferred_choices' => ['EUR', 'GBP', 'USD']
                ])
                ->add('budgeted', MoneyType::class, [
                    'label' => 'Budgeted',
                    'grouping' => true, // NOTE: Currency digit grouping; it is needed for validation to pass
                    'currency' => $subject->getCurrency(),
                    'required' => true,
                ])
            ->end()
        ;
    }

    // MARK: - Show Fields
    protected function configureShowFields(ShowMapper $show): void
    {
        /** @var BudgetItem $subject */
        $subject = $this->getSubject();
        $currency = $subject->getCurrency();
        $title = $subject->__toString();

        $show
            ->with('', [
                'label' => $title,
                'class' => 'col-md-6'
            ])
                ->add('budget.name', null, [
                    'label' => 'Budget'
                ])
                ->add('budgetSubCategory', null, [
                    'label' => 'Category (Main Category)'
                ])
                // ->add('unit.name')
                ->add('currency')
                ->add('budgeted', MoneyType::class, [
                    'label' => 'Budgeted',
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $currency
                ])
                ->add('actual', MoneyType::class, [
                    'label' => 'Actual Expenses',
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $currency
                ])
                ->add('leftOver', MoneyType::class, [
                    'label' => 'Left / Over',
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $currency
                ])
            ->end()
        ;
    }

    // MARK: - PrePersist
    protected function prePersist(object $object): void
    {
        /** @var BudgetItem $object  */

        /* TODO: create UserService */
        // $currentUser = $this->userService->getCurrentUser();

        // /* Get unit */
        // $unitId = $this->getUnitId();

        /* Get logged user data */
        // $currentUserData = $this->getCurrentUserData();

        // if ($unitId) {
        //     /** @var UnitRepository $unitRepository */
        //     $unitRepository = $this->entityManager->getRepository(Unit::class);

        //     $unit = $unitRepository->findOneBy([
        //         'id' => $unitId
        //     ]);

        //     $object->setUnit($unit);
        // }

        // $object->setAddedByUser($currentUser);
    }

    // MARK: - PreUpdate
    protected function preUpdate(object $object): void
    {
        /** @var Budget $object  */

        /* TODO: create UserService */
        // $currentUser = $this->userService->getCurrentUser();

        // $object->setEditedByUser($currentUser);
    }

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
        //         ->andWhere($rootAlias . '.unit = :unitId')
        //         ->setParameter('unitId', $unitId)
        //         ->addOrderBy($rootAlias.'.budgetSubCategory', 'ASC')
        //     ;
        // } else {
        //     $qb
        //         ->join($rootAlias . '.unit', 'u')
        //         ->andWhere('u.active = :active')
        //         ->setParameter('active', true)
        //         ->addOrderBy($rootAlias.'.budgetSubCategory', 'ASC')
        //     ;
        // }

        return $query;
    }
}
