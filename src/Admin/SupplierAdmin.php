<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Supplier;
use App\Entity\Unit;
use App\Traits\AdminTrait;
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
use Sonata\Form\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class SupplierAdmin extends AbstractAdmin
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
        //     ->add('comments', $this->getRouterIdParameter().'/comments')
        // ;
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
            ->add('name', null, [
                'advanced_filter' => false,
            ])
            ->add('currency', null, [
                'label' => 'Currency',
                'field_type' => CurrencyType::class,
                'advanced_filter' => false,
                'field_options' => [
                    'preferred_choices' => $this->preferredCurrencyChoices,
                ]
            ])
            ->add('unit', null, [
                'advanced_filter' => false,
            ])
        ;

        // if (!$unit) {
        //     $filter
        //         ->add('unit', null, [
        //             'label' => 'Unit',
        //             'field_type' => EntityType::class,
        //             'show_filter' => true,
        //             'field_options' => [
        //                 'class' => Unit::class,
        //                 'choice_label' => 'name',
        //                 'query_builder' => function (EntityRepository $er) {
        //                     return $er->createQueryBuilder('s')
        //                         ->andWhere('s.active = :active')
        //                         ->orderBy('s.name', 'ASC')
        //                         ->setParameter('active', true);
        //                 },
        //             ],
        //         ])
        //     ;
        // }
    }

    // MARK: - List Fields
    protected function configureListFields(ListMapper $list): void
    {
        // /* Get unit id */
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

        // $requestQueryPcode = $this->getRequest()->query->get('pcode');

        /* Get supplier list if it is requested from the purchase form */
        // if ($requestQueryPcode == 'admin.purchase') {
        //     $list
        //         ->addIdentifier('name', null, [
        //             'header_style' => 'width: 20%',
        //         ])
        //     ;

        //     if (!$unitId) {
        //         $list
        //             ->add('unit.name', null, [
        //                 'label' => 'Unit',
        //                 'header_style' => 'width: 20%',
        //             ])
        //         ;
        //     }

        //     $list
        //         ->add('currency', null, [
        //             'header_style' => 'width: 20%',
        //         ])
        //     ;
        // } else {
            $list
                ->addIdentifier('name')
                ->add('unit')
            // ;

            // if (!$unitId) {
            //     $list
            //         ->add('unit.name', null, [
            //             'label' => 'Unit',
            //             'header_style' => 'width: 10%',
            //         ]);
            // }

            // $list
                ->add('numberOfInvoices', null, [
                    'label' => 'Invoices',
                    'header_style' => 'width:5%; text-align:center;',
                    'row_align' => 'center',
                ])
                ->add('numberOfUnpaidInvoices', null, [
                    'label' => 'Unpaid',
                    'header_style' => 'width:5%; text-align:center;',
                    'row_align' => 'center',
                    'template' => 'Supplier/unpaid_balance_list_string.html.twig'
                ])
                ->add('currency', null, [
                    'header_style' => 'width:5%; text-align:center;',
                    'row_align' => 'center',
                ])
                ->add('amountOfUnpaidInvoices', MoneyType::class, [
                    'label' => 'Balance',
                    'header_style' => 'width:8%; text-align:right;',
                 'row_align' => 'right',
                    'template' => 'Supplier/unpaid_balance_list_string.html.twig'
                ])
                ->add(ListMapper::NAME_ACTIONS, null, [
                    'header_style' => 'width: 25%;',
                    'actions' => $actions,
                ])
                // ->add('comments', null, [
                //     'header_style' => 'width: 6%',
                //     'template' => 'CRUD/list_comments.html.twig',
                // ])
            ;
        // }
    }

    // MARK: - Form Fields
    protected function configureFormFields(FormMapper $form): void
    {
        // /* Get unit id */
        // $unitId = $this->getUnitId();

        $editRoute = $this->isCurrentRoute('edit');

        if ($editRoute) {
            /** @var Supplier $subject  */
            $subject = $this->getSubject();
            $supplierName = $subject->getName();
        }

        $form
            ->with('First Column', [
                'label' => $editRoute ? $supplierName : 'Add New Supplier',
                'class' => 'col-md-5'
            ])
            ->add('name')
        ;

        // if (!$unitId) {
        //     $form
        //         ->add('unit', EntityType::class, [
        //             'class' => Unit::class,
        //             'choice_label' => 'name',
        //             'placeholder' => 'Choose an option',
        //             'query_builder' => function (EntityRepository $er) {
        //                 return $er->createQueryBuilder('s')
        //                     ->andWhere('s.active = :active')
        //                     ->orderBy('s.name', 'ASC')
        //                     ->setParameter('active', true)
        //                 ;
        //             },
        //         ])
        //     ;
        // }

        $form
                ->add('currency', CurrencyType::class, [
                    'placeholder' => 'Choose an option',
                    'preferred_choices' => $this->preferredCurrencyChoices
                ])
                ->add('address')
                ->add('phoneNumber')
                ->add('mobileNumber')
                ->add('email')
                ->add('website')
                ->add('contactName')
                ->add('jobTitle')
            ->end()
            ->with('Second Column', [
                'label' => false,
                'class' => 'col-md-5'
            ])
                ->add('vatNumber')
                ->add('vatRate')
                ->add('bankAccountName')
                ->add('bankAccountNumber')
                ->add('iban')
                ->add('sortCode')
                ->add('bicCode')
                ->add('supplierTerms')
            ->end()
            ->with('Unit', [
                'class' => 'col-md-2'
            ])
                ->add('unit', EntityType::class, [
                    'class' => Unit::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Choose an option',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('s')
                            ->andWhere('s.active = :active')
                            ->orderBy('s.name', 'ASC')
                            ->setParameter('active', true)
                        ;
                    },
                ])
            ->end()
            // ->with('Comments',['class' => 'col-sm-12'])
            //     ->add('comments', CollectionType::class, [
            //         'label' => false,
            //         'required' => false,
            //     ], [
            //         'edit' => 'inline',
            //         'inline' => 'form',
            //         'sortable'  => 'position',
            //     ])
            // ->end()
        ;
    }

    // MARK: - Show Fields
    protected function configureShowFields(ShowMapper $show): void
    {
        /** @var Supplier $subject  */
        $subject = $this->getSubject();

        $supplierName = $subject->getName();

        $show
            ->with('First Column', [
                'label' => $supplierName,
                'class' => 'col-md-6'
            ])
                ->add('name')
                ->add('unit.name')
                ->add('currency')
                ->add('address')
                ->add('phoneNumber')
                ->add('mobileNumber')
                ->add('email')
                ->add('website')
                ->add('contactName')
                ->add('jobTitle')
            ->end()
            ->with('Second Column', [
                'label' => false,
                'class' => 'col-md-6'
            ])
                ->add('vatNumber')
                ->add('vatRate')
                ->add('bankAccountName')
                ->add('bankAccountNumber')
                ->add('iban')
                ->add('sortCode')
                ->add('bicCode')
                ->add('supplierTerms')
            ->end()
            ->with('Invoices')
                ->add('invoices', null, [
                    'associated_property' => 'invoiceNumber',
                    'template' => 'Supplier/invoice_supplier_show_one_to_many.html.twig'
                ])
            ->end()
            ->with('Purchases')
                ->add('purchases', null, [
                    'associated_property' => 'id',
                    'template' => 'Supplier/purchase_supplier_show_one_to_many.html.twig'
                ])
            ->end()
        ;

        // if ($subject->getComments()->count() > 0) {
        //     $show
        //         ->with('Comments', [
        //             'label' => 'Comments',
        //         ])
        //             ->add('comments', CollectionType::class, [
        //                 'template' => 'Comments/comments_show_field.html.twig',
        //             ])
        //         ->end()
        //     ;
        // }
    }

    // MARK: - PrePersist
    protected function prePersist(object $object): void
    {
        /** @var Supplier $object  */

        // /** @var UnitRepository $unitRepository */
        // $unitRepository = $this->entityManager->getRepository(Unit::class);

        // /* Get unit id */
        // $unitId = $this->getUnitId();

        // if ($unitId) {
        //     $unit = $unitRepository->findOneBy(['id' => $unitId]);

        //     /* Persist created data */
        //     $object->setUnit($unit);
        // }

        // /* Adding comment(s) management */
        // $this->manageEmbeddedCommentAdmin($object);
    }

    // MARK: - PreUpdate
    protected function preUpdate(object $supplier): void
    {
        /** @var Supplier $supplier  */

        // /* Adding comment(s) management */
        // $this->manageEmbeddedCommentAdmin($supplier);
    }

    // MARK: - Configure Query
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        if (!$query instanceof ProxyQuery) {
            throw new InvalidArgumentException('Expected an instance of ProxyQuery');
        }

        $qb = $query->getQueryBuilder();
        $rootAlias = current($query->getQueryBuilder()->getRootAliases());

        // /* Get unit id */
        // $unitId = $this->getUnitId();

        // if ($unitId) {
        //     $qb
        //         ->leftJoin($rootAlias . '.invoices', 'i')
        //         ->addSelect('COUNT(i.id) numberOfInvoices')
        //         ->andWhere($rootAlias . '.unit = :unitId')
        //         ->setParameter('unitId', $unitId)
        //         ->addGroupBy($rootAlias)
        //     ;
        // } else {
            $qb
                ->leftJoin($rootAlias . '.invoices', 'i')
                ->join($rootAlias . '.unit', 's')
                ->addSelect('COUNT(i.id) numberOfInvoices')
                ->where('s.active = :active')
                ->setParameter('active', true)
                ->addGroupBy($rootAlias)
            ;
        // }

        return $query;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
        $sortValues[DatagridInterface::SORT_BY] = 'name';
    }
}
