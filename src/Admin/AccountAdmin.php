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
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\Form\Type\CollectionType;

final class AccountAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        // $collection
        //     ->add('redirectToCashTransfer', $this->getRouterIdParameter().'/cash_transfer')
        //     ->add('redirectToPurchase', $this->getRouterIdParameter().'/purchase')
        //     ->add('redirectToCashWithdrawal', $this->getRouterIdParameter().'/cash_withdrawal')
        //     ->add('redirectToCurrencyExchange', $this->getRouterIdParameter().'/currency_exchange')
        //     ->add('redirectToBankTransfer', $this->getRouterIdParameter().'/bank_transfer')
        //     ->add('addFundsLinkToModal', $this->getRouterIdParameter().'/addFundsLinkToModal')
        //     ->add('addFunds', $this->getRouterIdParameter().'/addFunds')
        // ;
    }

    // /* Remove batch delete action from the list */
    // protected function configureBatchActions($actions): array
    // {
    //     unset($actions['delete']);

    //     return $actions;
    // }

    // /* Remove Download button from bottom of the list */
    // public function getExportFormats(): array
    // {
    //     return [];
    // }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            // ->add('accountType')
            ->add('balance')
            ->add('currency', null, [
                'label' => 'Currency',
                'show_filter' => true,
                'field_type' => CurrencyType::class,
                'field_options' => [
                    'preferred_choices' => ['EUR', 'GBP', 'USD'],
                ]
            ])
            ->add('deactivated', null, [
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $actionsAdmin = [
            // 'addFunds' => [
            //     'template' => 'Account/list__action_add_funds.html.twig',
            // ],
            'show' => [],
            'edit' => [
                // 'template' => 'CRUD/list__action_edit_no_label.html.twig',
            ],
            'delete' => [],
        ];

        $list
            ->addIdentifier('nameWithCurrency', null, [
                'label' => 'Account Name',
                'route' => [
                    'name' => 'show'
                ],
            ])
            // ->add('accountType.name', null, [
            //     'label' => 'Account Type',
            // ])
            ->add('currency', null, [
				'row_align' => 'center',
				'header_style' => 'text-align: center',
			])
            ->add('balance', MoneyType::class, [
                // 'template' => 'CRUD/list_currency.html.twig',
				'row_align' => 'right',
				'header_style' => 'text-align: right',
            ])
            ->add('deactivated', null, [
                'label' => 'Active',
                'template' => 'Account/custom_deactivated_list.html.twig',
				'row_align' => 'center',
				'header_style' => 'text-align: center',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => $actionsAdmin,
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        // $accountTypes = ['Bank Account', 'Card Account', 'Cash Account'];

        $form
            ->with('Account', ['class' => 'col-md-4'])
                ->add('name')
                // ->add('accountType')
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
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        /** @var Account $subject */
        $subject = $this->getSubject();

        $accountName = $subject->getName();
        // $accountType = $subject->getAccountType();
        $currency = $subject->getCurrency();

        $show
            ->with('Account', [
                'label' => $accountName,
                'class' => 'col-md-6'
            ])
                ->add('name')
                // ->add('accountType', null, [
                //     'label' => 'Account Type'
                // ])
                ->add('currency')
                ->add('balance', MoneyType::class, [
                    'label' => 'Balance',
                    // 'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $currency
                ])
                ->add('deactivated', null, [
                    'label' => 'Active',
                    'template' => 'Account/custom_deactivated_show.html.twig',
                ])
            ->end()
        ;

        // if ($accountType !== 'Cash Account') {
        //     $show
        //         ->with('File Preview', [
        //             'label' => 'File Preview',
        //             'class' => 'col-md-6'
        //         ])
        //             ->add('file', null, [
        //                 'template' => 'CRUD/show_one_to_many_document_file.html.twig'
        //             ])
        //         ->end()
        //     ;
        // }

        // $show
        //     ->with('Transactions')
        //         ->add('mainAccountTransactions', CollectionType::class, [
        //             'template' => 'Account/transaction_account_show_one_to_many.html.twig'
        //         ])
        //     ->end()
        // ;
    }

    // /**
    //  * @param ProxyQueryInterface<T> $query
    //  *
    //  * @return ProxyQueryInterface<T>
    //  */
    // protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    // {
    //     if (!$query instanceof ProxyQueryInterface) {
    //         throw new InvalidArgumentException('Expected an instance of ProxyQueryInterface');
    //     }

    //     $qb = $query->getQueryBuilder();
    //     $rootAlias = current($query->getQueryBuilder()->getRootAliases());

    //     $object = '';

    //     $qb
    //         ->where($rootAlias . '.object = :object')
    //         ->setParameter('object', $object)
    //     ;

    //     return $query;
    // }

    protected function prePersist(object $object): void
    {
        /** @var Account $object */

        $object->setDeactivated(false);
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
        $sortValues[DatagridInterface::SORT_BY] = 'name';
    }
}
