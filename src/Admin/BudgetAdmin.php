<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Budget;
use App\Entity\BudgetType;
use App\Traits\AdminTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class BudgetAdmin extends AbstractAdmin
{
    use AdminTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        // $collection
        //     ->add('viewDocument', $this->getRouterIdParameter() . '/viewDocument')
        //     ->add('sendEmail', $this->getRouterIdParameter() . '/sendEmail')
        //     ->add('emailModal', $this->getRouterIdParameter().'/emailModal')
        // ;
    }

    // /* Remove batch delete action from the list */
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
            ->add('name', null, [
                'label' => 'Name',
                'show_filter' => true,
            ])
            // ->add('budgetType', null, [
            //     'placeholder' => 'Choose an option',
            //     'show_filter' => true,
            //     'field_type' => ChoiceType::class,
            //     'field_options' => [
            //         'choices' => BudgetType::NAMES,
            //     ],
            //     'query_builder' => function (EntityRepository $er) {
            //         return $er->createQueryBuilder('b')
            //             ->orderBy('b.budgetType', 'ASC');
            //     },
            // ])
            ->add('startDate')
            ->add('endDate')
            ->add('currency', null, [
                'label' => 'Currency',
                'show_filter' => true,
                'field_type' => CurrencyType::class,
                'field_options' => [
                    'preferred_choices' => $this->preferredCurrencyChoices,
                ]
            ])
            ->add('totalBudgeted')
            ->add('totalActual')
            ->add('leftOver')
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

        $list
            ->addIdentifier('name', null, [
                'label' => 'Budget Name',
                'route' => [
                    'name' => 'show'
                ],
            ])
            ->add('budgetTypeName', null, [
                'label' => 'Budget Type',
            ])
            ->add('startDate', 'date', [
                'label' => 'Start Date',
                'pattern' => 'd/M/Y',
            ])
            ->add('endDate', 'date', [
                'label' => 'End Date',
                'pattern' => 'd/M/Y',
            ])
            ->add('currency', null, [
                'row_align' => 'center',
                'header_style' => 'text-align: center',
            ])
            ->add('totalBudgeted', MoneyType::class, [
                'label' => 'Total Budgeted',
                'template' => 'CRUD/list_currency.html.twig',
                'row_align' => 'right',
                'header_style' => 'text-align: right',
            ])
            ->add('totalActual', MoneyType::class, [
                'label' => 'Total Actual',
                'template' => 'CRUD/list_currency.html.twig',
                'row_align' => 'right',
                'header_style' => 'text-align: right',
            ])
            ->add('leftOver', MoneyType::class, [
                'label' => 'Left / Over',
                'template' => 'CRUD/list_currency.html.twig',
                'row_align' => 'right',
                'header_style' => 'text-align: right',
            ])
        ;

        // if (!$unitId) {
        //     $list
        //         ->add('unit.name')
        //     ;
        // }

        $list
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

        $now = new DateTime();

        $form
            ->with('Budget', ['class' => 'col-md-6'])
                ->add('name')
                ->add('budgetType', ChoiceType::class, [
                    'placeholder' => 'Choose an option',
                    'choices' => BudgetType::NAMES,
                ])
                ->add('startDate', DatePickerType::class, [
                    'years' => range(1900, $now->format('Y')),
                    'required' => true,
                    'format' => 'dd.MM.yyyy',
                ])
                ->add('currency', CurrencyType::class, [
                    'placeholder' => 'Choose an option',
                    'preferred_choices' => $this->preferredCurrencyChoices
                ])
                /* TODO: After submitting the form,
                    the end date readonly field should be set to the last day of the month */
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
        /** @var Budget $subject */
        $subject = $this->getSubject();
        $budgetName = $subject->__toString();
        $currency = $subject->getCurrency();

        $show
            ->with('Budget', [
                'label' => $budgetName,
                'class' => 'col-md-6'
            ])
                ->add('name')
                ->add('budgetTypeName', null, [
                    'label' => 'Budget Type',
                ])
                // ->add('unit.name', null, [
                //     'label' => 'Unit'
                // ])
                ->add('startDate', null, [
                    'label' => 'Start Date',
                    'format' => 'd/m/Y'
                ])
                ->add('endDate', 'date', [
                    'label' => 'End Date',
                    'format' => 'd/m/Y'
                ])
                ->add('totalBudgeted', MoneyType::class, [
                    'label' => 'Total Budgeted',
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $currency
                ])
                ->add('totalActual', MoneyType::class, [
                    'label' => 'Total Actual',
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
        /** @var Budget $object  */

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

        $qb = $query->getQueryBuilder();
        $rootAlias = current($query->getQueryBuilder()->getRootAliases());

        $qb
            ->addOrderBy($rootAlias . '.startDate', 'ASC')
        ;

        // /* Get unit */
        // $unitId = $this->getUnitId();

        // if ($unitId) {
        //     $qb
        //         ->andWhere($rootAlias . '.unit = :unitId')
        //         ->setParameter('unitId', $unitId)
        //         ->addOrderBy($rootAlias.'.startDate', 'ASC')
        //     ;
        // } else {
        //     $qb
        //         ->join($rootAlias . '.unit', 'u')
        //         ->andWhere('u.active = :active')
        //         ->setParameter('active', true)
        //         ->addOrderBy($rootAlias.'.startDate', 'ASC')
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
