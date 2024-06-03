<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use App\Entity\Account;
use App\Entity\AccountType;
use App\Entity\Unit;
use App\Traits\AdminTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\Form\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class AccountAdmin extends AbstractAdmin
{
    use AdminTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    // MARK: ConfigureRoutes
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->add('redirectToCashTransfer', $this->getRouterIdParameter() . '/cash_transfer')
            ->add('redirectToPurchase', $this->getRouterIdParameter() . '/purchase')
            ->add('redirectToCashWithdrawal', $this->getRouterIdParameter() . '/cash_withdrawal')
            ->add('redirectToCurrencyExchange', $this->getRouterIdParameter() . '/currency_exchange')
            ->add('redirectToBankTransfer', $this->getRouterIdParameter() . '/bank_transfer')
            ->add('addFundsLinkToModal', $this->getRouterIdParameter() . '/addFundsLinkToModal')
            ->add('addFunds', $this->getRouterIdParameter() . '/addFunds')
        ;
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
        /* Get unit */
        // $unitId = $this->getUnitId();

        $filter
            ->add('name', null, [
                'advanced_filter' => false
            ])
            ->add('accountType', null, [
                'placeholder' => 'Choose an option',
                'show_filter' => true,
                'advanced_filter' => false,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => AccountType::NAMES_VALUE,
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->orderBy('a.accountType', 'ASC');
                },
            ])
            ->add('balance', null, [
                'advanced_filter' => false
            ])
            ->add('currency', null, [
                'label' => 'Currency',
                'show_filter' => true,
                'advanced_filter' => false,
                'field_type' => CurrencyType::class,
                'field_options' => [
                    'preferred_choices' => ['EUR', 'GBP', 'USD'],
                ]
            ])
            ->add('deactivated', null, [
                'show_filter' => true,
                'advanced_filter' => false
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

        $actions = [
            'addFunds' => [
                'template' => 'Account/list__action_add_funds.html.twig',
            ],
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
            ->addIdentifier('nameWithCurrency', null, [
                'label' => 'Account Name',
                'route' => [
                    'name' => 'show'
                ],
            ])
            ->add('accountTypeName', null, [
                'label' => 'Account Type',
            ])
            ->add('currency', null, [
                'header_style' => 'width: 5%; text-align: center',
                'row_align' => 'center',
            ])
            ->add('balance', MoneyType::class, [
                'template' => 'CRUD/list_currency.html.twig',
                'row_align' => 'right',
                'header_style' => 'text-align: right',
            ])
            ->add('deactivated', null, [
                'label' => 'Active',
                'header_style' => 'width: 5%; text-align: center',
                'row_align' => 'center',
                'template' => 'Account/custom_deactivated_list.html.twig',
            ])
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
            ->with('Account', ['class' => 'col-md-4'])
                ->add('name')
                ->add('accountType', ChoiceType::class, [
                    'placeholder' => 'Choose an option',
                    'choices' => AccountType::NAMES_STRING,
                ])
                ->add('currency', CurrencyType::class, [
                    'placeholder' => 'Choose an option',
                    'preferred_choices' => ['EUR', 'GBP', 'USD']
                ])
            ->end()
            ->with('Upload File', ['class' => 'col-md-4'])
                // ->add('file', CollectionType::class, [
                //     'label' => 'Upload file(s)',
                //     'required' => false,
                //     'help' => 'Supported file types: JPG/JPEG, PNG and PDF'
                //     ], [
                //         'edit' => 'inline',
                //         'inline' => 'form',
                //         'sortable'  => 'position',
                //     ])
            ->end()
            ->with('Deactivation', ['class' => 'col-md-4'])
                ->add('deactivated', null, [
                    'label' => 'Deactivate',
                    'help' => 'Check this box to disable the account for making payments on invoices and purchases.'
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
        //                 return $er->createQueryBuilder('s')
        //                     ->andWhere('s.active = :active')
        //                     ->orderBy('s.name', 'ASC')
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
        /** @var Account $subject */
        $subject = $this->getSubject();

        $accountName = $subject->getName();
        $accountTypeName = $subject->getAccountTypeName();
        $currency = $subject->getCurrency();

        $show
            ->with('Account', [
                'label' => $accountName,
                'class' => 'col-md-6'
            ])
                ->add('name')
                ->add('accountTypeName', null, [
                    'label' => 'Account Type',
                ])
                ->add('unit.name', null, [
                    'label' => 'Unit'
                ])
                ->add('currency')
                ->add('balance', MoneyType::class, [
                    'label' => 'Balance',
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $currency
                ])
                ->add('deactivated', null, [
                    'label' => 'Active',
                    'template' => 'Account/custom_deactivated_show.html.twig',
                ])
            ->end()
        ;

        if ($accountTypeName !== 'Cash' && $accountTypeName !== 'Wallet') {
            $show
                ->with('File Preview', [
                    'label' => 'File Preview',
                    'class' => 'col-md-6'
                ])
                    // ->add('file', null, [
                    //     'template' => 'CRUD/show_one_to_many_document_file.html.twig'
                    // ])
                ->end()
            ;
        }

        $show
            ->with('Transactions')
                ->add('mainAccountTransactions', CollectionType::class, [
                    'template' => 'Account/transaction_account_show_one_to_many.html.twig'
                ])
            ->end()
        ;
    }

    // MARK: - PrePersist
    protected function prePersist(object $account): void
    {
        /** @var Account $account */

        // /** @var UnitRepository $unitRepository */
        // $unitRepository = $this->entityManager->getRepository(Unit::class);

        // /* Get unit */
        // $unitId = $this->getUnitId();

        // if ($unitId) {
        //     $unit = $unitRepository->findOneBy(['id' => $unitId]);
        //     $account->setUnit($unit);
        // }

        $account->setDeactivated(false);
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
