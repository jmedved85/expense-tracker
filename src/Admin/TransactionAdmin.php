<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Account;
use App\Entity\AccountType;
use App\Entity\InvoicePartPayment;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Entity\Unit;
use App\Repository\AccountRepository;
use App\Repository\AccountTypeRepository;
use App\Repository\InvoicePartPaymentRepository;
use App\Repository\TransactionRepository;
use App\Service\ErrorHandler;
use App\Service\TransactionService;
use App\Traits\AdminTrait;
use App\Validator\Constraints\NotNegative;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Exception;
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
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Intl\Currencies;
use TypeError;

class TransactionAdmin extends AbstractAdmin
{
    use AdminTrait;

    protected TransactionService $transactionService;

    private ErrorHandler $errorHandler;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ErrorHandler $errorHandler,
        TransactionService $transactionService,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct(null, Transaction::class, null);
        $this->transactionService = $transactionService;
        $this->errorHandler = $errorHandler;
        $this->entityManager = $entityManager;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->add('addBankFeeLinkToModal', $this->getRouterIdParameter() . '/addBankFeeLinkToModal')
            ->add('addBankFee', $this->getRouterIdParameter() . '/addBankFee')
        ;
    }

    protected function configureActionButtons(
        array $buttonList,
        string $action,
        ?object $object = null
    ): array {
        $buttonList['create'] = false;

        if ($action == 'edit') {
            $buttonList['create'] = false;
        }

        return $buttonList;
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

        $excludedTransactionTypes = [TransactionType::FROM_ACCOUNT, TransactionType::TO_ACCOUNT];

        // if (!$unitId) {
        //     $filter
        //         ->add('mainAccount.unit', null, [
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

        $filter
            ->add('date', DateRangeFilter::class, [
                'field_type' => DateRangePickerType::class,
                'field_options' => [
                    'field_options' => [
                        'format' => 'dd.MM.yyyy'
                    ]
                ]
            ])
            ->add('transactionNumber')
            ->add('mainAccount', null, [
                'label' => 'Account',
                'show_filter' => true,
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Account::class,
                    'choice_label' => 'nameWithCurrencyBalance',
                    // 'choice_label' => !$unitId ? 'nameWithCurrencyUnitBalance' : 'nameWithCurrencyBalance',
                    // 'query_builder' => function (EntityRepository $er) use ($unitId) {
                    //     if ($unitId) {
                    //         return $er->createQueryBuilder('a')
                    //             ->andWhere('a.unit = :unitId')
                    //             ->orderBy('a.name', 'ASC')
                    //             ->setParameter('unitId', $unitId)
                    //         ;
                    //     } else {
                    //         return $er->createQueryBuilder('a')
                    //             ->join('a.unit', 'u')
                    //             ->andWhere('u.active = :active')
                    //             ->orderBy('a.name', 'ASC')
                    //             ->setParameter('active', true)
                    //         ;
                    //     }
                    // },
                ],
            ])
            ->add('transferFromAccount', null, [
                'label' => 'From',
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Account::class,
                    'choice_label' => 'nameWithCurrencyBalance',
                    // 'choice_label' => !$unitId ? 'nameWithCurrencyUnitBalance' : 'nameWithCurrencyBalance',
                    // 'query_builder' => function (EntityRepository $er) use ($unitId) {
                    //     if ($unitId) {
                    //         return $er->createQueryBuilder('a')
                    //             ->andWhere('a.unit = :unitId')
                    //             ->orderBy('a.name', 'ASC')
                    //             ->setParameter('unitId', $unitId)
                    //         ;
                    //     } else {
                    //         return $er->createQueryBuilder('a')
                    //             ->join('a.unit', 'u')
                    //             ->andWhere('u.active = :active')
                    //             ->orderBy('a.name', 'ASC')
                    //             ->setParameter('active', true)
                    //         ;
                    //     }
                    // },
                ],
            ])
            ->add('transferToAccount', null, [
                'label' => 'To',
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Account::class,
                    'choice_label' => 'nameWithCurrencyBalance',
                    // 'choice_label' => !$unitId ? 'nameWithCurrencyUnitBalance' : 'nameWithCurrencyBalance',
                    // 'query_builder' => function (EntityRepository $er) use ($unitId) {
                    //     if ($unitId) {
                    //         return $er->createQueryBuilder('a')
                    //             ->andWhere('a.unit = :unitId')
                    //             ->orderBy('a.name', 'ASC')
                    //             ->setParameter('unitId', $unitId)
                    //         ;
                    //     } else {
                    //         return $er->createQueryBuilder('a')
                    //             ->join('a.unit', 'u')
                    //             ->andWhere('u.active = :active')
                    //             ->orderBy('a.name', 'ASC')
                    //             ->setParameter('active', true)
                    //         ;
                    //     }
                    // },
                ],
            ])
            ->add('transactionType', ChoiceType::class, [
                'placeholder' => 'Choose an option',
                'choices' => TransactionType::NAMES,
            ])
            // ->add('transactionType', null, [
            //     'label' => 'Transaction Type',
            //     'field_type' => EntityType::class,
            //     'field_options' => [
            //         'class' => TransactionType::class,
            //         'choice_label' => 'name',
            //         'query_builder' => function (EntityRepository $er) use ($excludedTransactionTypes) {
            //             return $er->createQueryBuilder('tt')
            //                 ->where('tt.name NOT IN (:excludedTransactionTypes)')
            //                 ->orderBy('tt.name', 'ASC')
            //                 ->setParameter('excludedTransactionTypes', $excludedTransactionTypes)
            //             ;
            //         },
            //     ],
            // ])
            ->add('description', null, [
                'label' => 'Description'
            ])
            ->add('currency', null, [
                'label' => 'Currency',
                'show_filter' => true,
                'field_type' => CurrencyType::class,
                'field_options' => [
                    'preferred_choices' => $this->preferredCurrencyChoices,
                    'choice_label' => function ($currencyCode) {
                        $currencyName = Currencies::getName($currencyCode);

                        return sprintf('%s - %s', $currencyCode, $currencyName);
                    },
                ]
            ])
        ;
    }

    // MARK: - List Fields
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('date', null, [
                'pattern' => 'd/M/Y',
            ])
            ->add('transactionNumberString', null, [
                'label' => 'Txn No',
                'header_style' => 'width: 4%;'
            ])
            ->add('transactionTypeName', null, [
                'label' => 'Transaction Type',
            ])
            // ->add('transactionType.name', null, [
            //     'label' => 'Transaction Type',
            //     'template' => 'CRUD/no_value_list_string.html.twig',
            //     'header_style' => 'width: 8%;'
            // ])
            ->add('description', null, [
                'label' => 'Description',
                'header_style' => 'width: 230px;'
            ])
//            ->add('mainAccount', null, [
//                'label' => 'Account',
//                'header_style' => 'width: 9%;'
//            ])
            ->add('transferFromAccount', null, [
                'label' => 'From',
                'template' => 'Transaction/transaction_transfer_from_list_string.html.twig',
                'header_style' => 'width: 9%;'
            ])
            ->add('balanceTransferFromAccount', null, [
                'label' => 'Balance (From)',
                'template' => 'Transaction/transaction_balance_from_list.html.twig',
                'row_align' => 'right',
                'header_style' => 'text-align:right;',
            ])
            ->add('transferToAccount', null, [
                'label' => 'To',
                'template' => 'Transaction/transaction_transfer_to_list_string.html.twig',
                'header_style' => 'width: 9%;'
            ])
            ->add('balanceTransferToAccount', null, [
                'label' => 'Balance (To)',
                'template' => 'Transaction/transaction_balance_to_list.html.twig',
                'row_align' => 'right',
                'header_style' => 'text-align:right;',
            ])
            ->add('moneyIn', null, [
                'template' => 'Transaction/transaction_money_in.html.twig',
                'header_style' => 'width:6%; text-align:right;',
                'row_align' => 'right',
            ])
            ->add('moneyOut', null, [
                'template' => 'Transaction/transaction_money_out.html.twig',
                'header_style' => 'width:6%; text-align:right;',
                'row_align' => 'right',
            ])
            ->add('amount', MoneyType::class, [
                'template' => 'CRUD/list_amount.html.twig',
                'row_align' => 'right',
                'header_style' => 'text-align: right',
            ])
            // ->add('file', null, [
            //     'label' => 'Doc File(s)',
            //     'template' => 'CRUD/list_document_file.html.twig',
            //     'header_style' => 'width:6%; text-align:center;',
            //  'row_align' => 'center',
            // ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'addBankFee' => [
                        'template' => 'Transaction/list__action_transaction_add_bank_fee.html.twig',
                    ],
                    'show' => [
                        'template' => 'Transaction/list__action_transaction_show.html.twig',
                    ],
                    'edit' => [
                        'template' => 'Transaction/list__action_transaction_check_edit.html.twig',
                    ],
                    'delete' => [],
                ],
                'header_style' => 'width: 15%;'
            ])
        ;
    }

    // MARK: - Form Fields
    protected function configureFormFields(FormMapper $form): void
    {
        /** @var AccountTypeRepository $accountTypeRepository */
        $accountTypeRepository = $this->entityManager->getRepository(AccountType::class);

        $now = new DateTime();

        $editRoute = $this->isCurrentRoute('edit');

        if ($editRoute) {
            /** @var Transaction $subject */
            $subject = $this->getSubject();

            $transactionType = $subject->getTransactionTypeName();

            if (
                $transactionType == 'Account Charge'
                || $transactionType == 'Bank Payment'
                || $transactionType == 'Cash Payment'
                || $transactionType == 'Money Returned'
            ) {
                $accountCurrency = $subject->getCurrency();
            } else {
                if ($transactionType == 'Funds Transfer') {
                    $accountCurrency = $subject->getTransferToAccount()->getCurrency();
                } else {
                    $accountCurrency = $subject->getTransferFromAccount()->getCurrency();
                }
            }

            $bankAccountType = AccountType::BANK;

            if ($transactionType == 'Funds Transfer') {
                $subject = $this->getSubject();
                $unit = $subject->getUnit();
                $currency = $subject->getCurrency();

                $transactionData = [];
                $transactionData['transactionType'] = $transactionType;
                $transactionData['bankAccountType'] = $bankAccountType;
                // $transactionData['unit'] = $unit;
                $transactionData['currency'] = $currency;

                $this->session()->set('transactionData', $transactionData);

                $form
                    ->with('Add Funds', [
                        'label' => 'Adding funds to ' . $unit->getName(),
                        'class' => 'col-md-4'
                    ])
                        ->add('mainAccount', EntityType::class, [
                            'label' => 'To Account',
                            'class' => Account::class,
                            'choice_label' => 'nameWithCurrencyBalance',
                            'placeholder' => 'Select an Account',
                            // 'query_builder' => function (EntityRepository $er) use ($bankAccountType, $unit) {
                            //     return $er->createQueryBuilder('a')
                            //         ->andWhere('a.accountType = :bankAccountType')
                            //         ->andWhere('a.deactivated = false')
                            //         ->andWhere('a.unit = :unit')
                            //         ->setParameters([
                            //             'bankAccountType' => $bankAccountType,
                            //             'unit' => $unit->getId()
                            //         ])
                            //         ->orderBy('a.currency', 'ASC')
                            //         ->addOrderBy('a.name', 'ASC');
                            // },
                        ])
                        ->add('date', DatePickerType::class, [
                            'label' => 'Date',
                            'years' => range(1900, $now->format('Y')),
                            'dp_max_date' => $now->format('c'),
                            'format' => 'dd.MM.yyyy',
                        ])
                        ->add('description', TextareaType::class, [
                            'required' => false,
                            'label' => 'Description',
                        ])
                    ->end()
                    ->with('Payments', [
                        'class' => 'col-md-4 mt-5'
                    ])
                        ->add('amount', MoneyType::class, [
                            'label' => 'Amount',
                            // NOTE: Currency digit grouping; it is needed for validation to pass
                            'grouping' => true,
                            'currency' => $currency,
                            'attr' => [
                                'style' => 'text-align:end;',
                            ],
                            'required' => true,
                        ])
                        ->add('bankFeeAmount', MoneyType::class, [
                            'label' => 'Bank Fee',
                            // NOTE: Currency digit grouping; it is needed for validation to pass
                            'grouping' => true,
                            'currency' => $currency,
                            'attr' => [
                                'style' => 'text-align:end;',
                            ],
                            'required' => false,
                        ])
                        ->add('bankFeeNotAdded', null, [
                            'label' => 'Bank Fee Not Applicable',
                            'required' => false,
                        ])
                    ->end()
                    ->with('File Uploads', [
                        'class' => 'col-md-4 mt-5'
                    ])
                        // ->add('file', CollectionType::class, [
                        //     'label' => 'Upload file(s)',
                        //     'required' => false,
                        //     'help' => 'Supported file types: JPG/JPEG, PNG and PDF'
                        // ], [
                        //     'edit' => 'inline',
                        //     'inline' => 'form',
                        //     'sortable'  => 'position',
                        // ])
                    ->end()
                ;
            } elseif ($transactionType == 'Account Charge') {
                $transactionNumber = '';

                if ($subject->getInvoice()) {
                    $invoiceTransactions = $subject->getInvoice()->getTransactions();

                    foreach ($invoiceTransactions as $transaction) {
                        if ($transaction->getTransactionTypeName() == 'Bank Payment') {
                            $transactionNumber = $transaction->getTransactionNumber();
                        }
                    }
                } else {
                    $transactionNumber = $subject->getTransactionNumber();
                }

                $form
                    ->with('Transaction - Bank Fee for Transaction No. ' . $transactionNumber, [
                        'class' => 'col-md-5'
                    ])
                        ->add('date', DatePickerType::class, [
                            'years' => range(1900, $now->format('Y')),
                            'dp_max_date' => $now->format('c'),
                            'required' => true,
                            'format' => 'dd.MM.yyyy',
                        ])
                        ->add('description', TextareaType::class, [
                            'required' => false,
                            'label' => 'Description',
                        ])
                        ->add('currency', TextType::class, [
                            'disabled' => true,
                        ])
                        /* Second option for display full Currency name */
                        //  ->add('currency', CurrencyType::class, [
                        //     'placeholder' => 'Choose an option',
                        //     'preferred_choices' => $this->preferredCurrencyChoices,
                        //     'disabled' => true
                        // ])
                        ->add('amount', MoneyType::class, [
                            'label' => 'Amount',
                            'grouping' => true, // NOTE: Currency digit grouping; it is needed for validation to pass
                            'currency' => $accountCurrency,
                            'required' => true,
                            'constraints' => [
                                new NotNegative(),
                            ],
                            'attr' => [
                                'style' => 'text-align:end;',
                            ],
                        ])
                    ->end()
                ;
            /* Only for Invoice Part-Payments */
            } elseif ($transactionType == 'Bank Payment') {
                $invoiceNumber = trim($subject->getInvoice()->getInvoiceNumber());
                $heading = "Part-Payment for Invoice nr. \"$invoiceNumber\"";

                $form
                    ->with('Heading', [
                        'class' => 'col-md-5',
                        'label' => $heading
                    ])
                        ->add('transactionType', ChoiceType::class, [
                            'placeholder' => 'Choose an option',
                            'choices' => TransactionType::NAMES,
                        ])
                        ->add('date', DatePickerType::class, [
                            'years' => range(1900, $now->format('Y')),
                            'dp_max_date' => $now->format('c'),
                            'format' => 'dd.MM.yyyy',
                            // 'disabled' => true
                        ])
                        ->add('mainAccount', EntityType::class, [
                            'class' => Account::class,
                            'label' => 'Account',
                            'choice_label' => 'nameWithCurrency',
                            'disabled' => true
                        ])
                        ->add('description', TextareaType::class, [
                            'required' => false,
                            'label' => 'Description',
                        ])
                        ->add('currency', CurrencyType::class, [
                            'placeholder' => 'Choose an option',
                            'disabled' => true
                        ])
                        ->add('amount', MoneyType::class, [
                            'label' => 'Amount',
                            // NOTE: Currency digit grouping; it is needed for validation to pass
                            'grouping' => true,
                            'currency' => $accountCurrency,
                            // 'disabled' => true
                            // 'required' => true
                            'constraints' => [
                                new NotNegative(),
                            ],
                            'attr' => [
                                'style' => 'text-align:end;',
                            ],
                        ])
                        // ->add('file', CollectionType::class, [
                        //     'label' => 'Upload file(s)',
                        //     'required' => false,
                        //     'help' => 'Supported file types: JPG/JPEG, PNG and PDF'
                        // ], [
                        //     'edit' => 'inline',
                        //     'inline' => 'form',
                        //     'sortable'  => 'position',
                        // ])
                    ->end()
                ;
            } elseif ($transactionType == 'Money Returned') {
                $invoice = $subject->getInvoice();
                $invoiceTransactions = $invoice->getTransactions()->toArray();

                $bankPaymentTransaction = null;

                if (!empty($invoiceTransactions)) {
                    foreach ($invoiceTransactions as $transaction) {
                        if ($transaction->getTransactionType() == 'Bank Payment') {
                            $bankPaymentTransaction = $transaction;
                        }
                    }
                }

                $form
                    ->with('transactionNumber', [
                        'label' => 'Money Return on Invoice nr. '
                            . '"' . $subject->getInvoice()->getInvoiceNumber() . '"',
                        'class' => 'col-md-5'
                    ])
                        ->add('account', null, [
                            'label' => 'Account',
                            'data' => $subject->getMainAccount()->getNameWithCurrencyBalance(),
                            'mapped' => false,
                            'required' => false,
                            'attr' => [
                                'disabled' => true,
                                'readonly' => true,
                            ],
                        ])
                        ->add('invoiceNumber', null, [
                            'label' => 'Invoice Number',
                            'data' => $subject->getInvoice()->getInvoiceNumber(),
                            'mapped' => false,
                            'required' => false,
                            'attr' => [
                                'disabled' => true,
                                'readonly' => true,
                            ],
                        ])
                        ->add('transactionNumber', NumberType::class, [
                            'label' => 'Bank Payment Transaction No',
                            'data' => $bankPaymentTransaction->getTransactionNumber(),
                            'mapped' => false,
                            'required' => false,
                            'attr' => [
                                'disabled' => true,
                                'readonly' => true,
                            ],
                        ])
                        ->add('transactionType', ChoiceType::class, [
                            'placeholder' => 'Choose an option',
                            'choices' => TransactionType::NAMES,
                        ])
                        // ->add('transactionType', EntityType::class, [
                        //     'class' => TransactionType::class,
                        //     'label' => 'Transaction Type',
                        //     'query_builder' => function (EntityRepository $er) {
                        //         return $er->createQueryBuilder('tt')
                        //             ->where('tt.name = :moneyReturned')
                        //             ->setParameter('moneyReturned', 'Money returned');
                        //     },
                        //     'attr' => [
                        //         'style' => 'display:none;',
                        //     ],
                        // ])
                        ->add('date', DatePickerType::class, [
                            'label' => 'Date',
                            'years' => range(1900, $now->format('Y')),
                            'dp_max_date' => $now->format('c'),
                            'required' => false,
                            'format' => 'dd.MM.yyyy',
                        ])
                        ->add('description', TextareaType::class, [
                            'required' => false,
                            'label' => 'Description',
                        ])
                        ->add('amount', MoneyType::class, [
                            'label' => 'Amount',
                            // NOTE: Currency digit grouping; it is needed for validation to pass
                            'grouping' => true,
                            'attr' => [
                                'style' => 'text-align:end;',
                            ],
                            'required' => true,
                        ])
                    ->end()
                    ->with('File Uploads', ['class' => 'col-md-5 mt-5'])
                        // ->add('file', CollectionType::class, [
                        //         'label' => false,
                        //         'required' => false,
                        //         'help' => 'Supported file types: JPG/JPEG, PNG and PDF',
                        //         'label_attr' => ['data-class' => 'lb-big'],
                        //         'by_reference' => false,
                        //     ], [
                        //         'edit' => 'inline',
                        //         'inline' => 'form',
                        //         'sortable'  => 'position',
                        //     ])
                    ->end()
                ;
            }
        } else {
            /* TODO: simplify the transactionData session operation, put in separate service */
            $transactionData = $this->session()->get('transactionData');
            ;

            if (isset($transactionData['transactionType'])) {
                $transactionType = $transactionData['transactionType'];

                if ($transactionType == 'Funds Transfer') {
                    $bankAccountType = $transactionData['bankAccountType'];
                    $unit =  $transactionData['unit'];
                    $currency =  $transactionData['currency'];

                    $form
                        ->with('Add Funds', [
                            'label' => 'Funds transfer to ' . $unit->getName(),
                            'class' => 'col-md-4'
                        ])
                        ->add('mainAccount', EntityType::class, [
                            'label' => 'To Account',
                            'class' => Account::class,
                            'choice_label' => 'nameWithCurrencyBalance',
                            'placeholder' => 'Select an Account',
                            // 'query_builder' => function (EntityRepository $er) use ($bankAccountType, $unit) {
                            //     return $er->createQueryBuilder('a')
                            //         ->andWhere('a.accountType = :bankAccountType')
                            //         ->andWhere('a.deactivated = false')
                            //         ->andWhere('a.unit = :unit')
                            //         ->setParameters([
                            //             'bankAccountType' => $bankAccountType,
                            //             'unit' => $unit->getId()
                            //         ])
                            //         ->orderBy('a.currency', 'ASC')
                            //         ->addOrderBy('a.name', 'ASC');
                            // },
                        ])
                        ->add('date', DatePickerType::class, [
                            'label' => 'Date',
                            'years' => range(1900, $now->format('Y')),
                            'dp_max_date' => $now->format('c'),
                            'format' => 'dd.MM.yyyy',
                        ])
                        ->add('description', TextareaType::class, [
                            'required' => false,
                            'label' => 'Description',
                        ])
                        ->end()
                        ->with('Payments', [
                            'class' => 'col-md-4 mt-5'
                        ])
                        ->add('amount', MoneyType::class, [
                            'label' => 'Amount',
                            // NOTE: Currency digit grouping; it is needed for validation to pass
                            'grouping' => true,
                            'currency' => $currency,
                            'attr' => [
                                'style' => 'text-align:end;',
                            ],
                            'required' => true,
                        ])
                        ->add('bankFeeAmount', MoneyType::class, [
                            'label' => 'Bank Fee',
                            // NOTE: Currency digit grouping; it is needed for validation to pass
                            'grouping' => true,
                            'currency' => $currency,
                            'attr' => [
                                'style' => 'text-align:end;',
                            ],
                            'required' => false,
                        ])
                        ->add('bankFeeNotAdded', null, [
                            'label' => 'Bank Fee Not Applicable',
                            'required' => false,
                        ])
                        ->end()
                        ->with('File Uploads', [
                            'class' => 'col-md-4 mt-5'
                        ])
                            // ->add('file', CollectionType::class, [
                            //     'label' => 'Upload file(s)',
                            //     'required' => false,
                            //     'help' => 'Supported file types: JPG/JPEG, PNG and PDF'
                            // ], [
                            //     'edit' => 'inline',
                            //     'inline' => 'form',
                            //     'sortable'  => 'position',
                            // ])
                        ->end()
                    ;
                }
            }
        }
    }

    // MARK: - Show Fields
    protected function configureShowFields(ShowMapper $show): void
    {
        /** @var Transaction $subject  */
        $subject = $this->getSubject();

        $transactionType = $subject->getTransactionType() ? $subject->getTransactionTypeName() : null;
        $currency = $subject->getCurrency();
        $accountFromCurrency = '';
        $accountToCurrency = '';
        $transactionNumber = null;

        if ($transactionType == 'Currency Exchange' || $transactionType == 'Bank Transfer') {
            $accountFromCurrency = $subject->getTransferFromAccount()->getCurrency();
            $accountToCurrency = $subject->getTransferToAccount()->getCurrency();
        }

//        if ($subject->getTransactions()->toArray()) {
//            $bankFeeTransaction = $subject->getTransactions()->toArray()[0];
//            $bankFeeCurrency = $bankFeeTransaction->getCurrency();
//        }

        if ($subject->getTransaction()) {
            $transactionNumber = $subject->getTransaction()->getTransactionNumber();
        }

        switch ($transactionType) {
            case 'Currency Exchange':
                $label = 'Transaction - Currency Exchange';

                break;
            case 'Bank Transfer':
                $label = 'Transaction - Bank Transfer';

                break;
            case 'Cash Withdrawal':
                $label = 'Cash Withdrawal - ' . $subject->getMainAccount()->getNameWithCurrency();

                break;
            case 'Cash Transfer':
                if ($subject->getTransferFromAccount()) {
                    $label = 'Cash Transfer - '
                        . $subject->getMainAccount()->getNameWithCurrency() . ' to '
                        . $subject->getTransferToAccount()->getNameWithCurrency()
                    ;
                } else {
                    $label = 'Cash Transfer to '
                        . $subject->getMainAccount()->getNameWithCurrency()
                    ;
                }

                break;
            case 'Account Charge':
                $label = 'Account charge for Transaction No. ' . $transactionNumber;

                break;

            case 'Money Returned':
                $invoiceNumber = $subject->getTransaction()->getInvoice()->getInvoiceNumber();
                $label = 'Money Returned on Invoice nr. ' . $invoiceNumber;

                break;
            default:
                $label = 'Transaction - ' . $subject->getMainAccount()->getNameWithCurrency();

                break;
        }

        $show
            ->with('Transaction', [
                'label' =>  $label,
                'class' => 'col-md-6'
            ])
                ->add('transactionNumberString', null, [
                    'label' => 'Transaction No.',
                ])
                ->add('date', null, [
                    'format' => 'd/m/Y'
                ])
        ;

        $show
            ->add('transactionTypeName', null, [
                'label' => 'Transaction Type',
            ])
            ->add('description', null, [
                'label' => 'Description'
            ])
        ;

        if ($transactionType == 'Cash Withdrawal' || $transactionType == 'Cash Transfer') {
            if ($subject->getTransferFromAccount()) {
                $currencyAmountFrom = $subject->getTransferFromAccount()->getCurrency();

                $show
                    ->add('transferFromAccount', null, [
                        'label' => 'From'
                    ])
                    ->add('amountFromAccount', null, [
                        'label' => 'Amount From Account',
                        'template' => 'CRUD/show_currency.html.twig',
                        'currency' => $currencyAmountFrom
                    ])
                    ->add('balanceTransferFromAccount', MoneyType::class, [
                        'label' => 'Balance (From Acc.)',
                        'template' => 'CRUD/show_currency.html.twig',
                        'currency' => $currency
                    ])
                ;
            } else {
                $currencyAmountFrom = null;
            }

            $show
                ->add('transferToAccount', null, [
                    'label' => 'To'
                ])
                ->add('balanceTransferToAccount', MoneyType::class, [
                    'label' => 'Balance (To Acc.)',
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $currency
                ])
                ->add('moneyIn', MoneyType::class, [
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $currency
                ])
            ;

            if ($subject->getTransferFromAccount()) {
                $show
                    ->add('moneyOut', MoneyType::class, [
                        'template' => 'CRUD/show_currency.html.twig',
                        'currency' => $currency
                    ])
                ;
            }
        }

        if ($transactionType == 'Currency Exchange' || $transactionType == 'Bank Transfer') {
            $bankFeeCurrency = $subject->getBankFeeCurrency();

            $show
                ->add('transferFromAccount.nameWithCurrencyBalance', null, [
                    'label' => 'From'
                ])
                ->add('transferToAccount.nameWithCurrencyBalance', null, [
                    'label' => 'To'
                ])
                ->add('amount', MoneyType::class, [
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $accountFromCurrency
                ])
                ->add('newValue', MoneyType::class, [
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $accountToCurrency
                ])
                ->add('bankFeeAmount', MoneyType::class, [
                    'label' => 'Bank Fee',
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $bankFeeCurrency
                ])
            ;
        }

        if ($transactionType == 'Funds Transfer') {
            $show
                ->add('currency')
                ->add('moneyIn', MoneyType::class, [
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $currency
                ])
                ->add('balanceTransferToAccount', MoneyType::class, [
                    'label' => 'Balance (To Acc.)',
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $currency
                ])
            ;
        }

        if ($transactionType == 'Account Charge' || $transactionType == 'Money Returned') {
            $show
                ->add('currency')
                ->add('amount', MoneyType::class, [
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $currency
                ])
                ->add('balanceMainAccount', MoneyType::class, [
                    'label' => 'Balance',
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $currency
                ])
            ;
        }

        $show
                ->add('addedByUserDateTime', null, [
                    'label' => 'Added By'
                ])
            ->end()
            ->with('Second column', [
                'label' => 'File Preview',
                'class' => 'col-md-6 mt-5'
            ])
                // ->add('file', null, [
                //     'label' => false,
                //     'template' => 'CRUD/show_one_to_many_document_file.html.twig'
                // ])
            ->end()
        ;
    }

    // MARK: - PrePersist
    /**
     * @throws Exception
     */
    protected function prePersist(object $object): void
    {
        /** @var Transaction $object */

        /** @var UnitRepository $unitRepository */
        $unitRepository = $this->entityManager->getRepository(Unit::class);
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->entityManager->getRepository(Account::class);
        /** @var InvoicePartPaymentRepository $invoicePartPaymentRepository */
        $invoicePartPaymentRepository = $this->entityManager->getRepository(InvoicePartPayment::class);
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        $userAndDateTime = $this->getUserAndDateTime();
        $bankFeeTransactionType = TransactionType::ACCOUNT_CHARGE;

        if ($this->session()->get('accountData')) {
            $accountData = $this->session()->get('accountData');
            $this->session()->remove('accountData');
        }

        // /* Get unit */
        // $unitId = $this->getUnitId();

        // if ($unitId) {
        //     $unit = $unitRepository->findOneBy([
        //         'id' => $unitId
        //     ]);
        // } elseif
        if (isset($accountData)) {
            $unit = $unitRepository->findOneBy([
                'id' => $accountData['unitId']
            ]);
        } else {
            $unit = $object->getUnit();
        }

        $object->setTransactionNumber($this->getAutoTransactionNumber($unit));
        $object->setUnit($unit);

        /* Get Entity data for redirectedFrom account */
        if (isset($accountData)) {
            $account =  $accountRepository->find($accountData['accountId']);
        } else {
            $account = $object->getMainAccount();
        }

        $object->setDateTimeAdded($userAndDateTime['dateTime']);
        $object->setAddedByUser($userAndDateTime['user']);

        /* Get data from form */
        $transactionType = $object->getTransactionTypeName();
        $amount = $object->getAmount();
         // If cash is withdrawn from card account in another currency
        $amountFromAccount = $object->getAmountFromAccount();
        $newValue = $object->getNewValue();

        /* Save accounts from/to based on transactionType & balance updates */
        if ($transactionType == 'Cash Transfer') {
            $accountFrom = $account;
            $accountTo = $object->getTransferToAccount();
            $accountFrom->setBalance($amount, false);
            $accountTo->setBalance($amount, true);

            $lastTransactionBalanceTransferFrom = $this->getLastTransactionBalance(
                $object->getDate(),
                $accountFrom,
                $object->getTransactionNumber()
            );

            $lastTransactionBalanceTransferTo = $this->getLastTransactionBalance(
                $object->getDate(),
                $accountTo,
                $object->getTransactionNumber()
            );

            $balanceTransferFrom = floatval($lastTransactionBalanceTransferFrom) - floatval($amount);
            $balanceTransferTo = floatval($lastTransactionBalanceTransferTo) + floatval($amount);

            $object->setCurrency($account->getCurrency());
            $object->setBankFeeNotApplicable(true);
            $object->setMoneyOut($amount);
            $object->setMainAccount($accountFrom);
            $object->setTransferFromAccount($accountFrom);
            $object->setTransferToAccount($accountTo);
            $object->setBalanceMainAccount(strval($balanceTransferFrom));
            $object->setBalanceTransferFromAccount(strval($balanceTransferFrom));
            $object->setBalanceTransferToAccount(strval($balanceTransferTo));

            $this->updateLaterTransactionsBalancesTransaction(
                $accountFrom,
                $object,
                $object->getDate(),
                $amount,
                false
            );
            $this->updateLaterTransactionsBalancesTransaction(
                $accountTo,
                $object,
                $object->getDate(),
                $amount,
                true
            );
        } elseif ($transactionType == 'Bank Transfer' || $transactionType == 'Currency Exchange') {
            $accountFrom = $account;
            $accountTo = $object->getTransferToAccount();

            $accountFromCurrency = $accountFrom->getCurrency();
            $accountToCurrency = $accountTo->getCurrency();

            $accountFrom->setBalance($amount, false);

            if ($accountFromCurrency == $accountToCurrency) {
                $accountTo->setBalance($amount, true);
            } else {
                $accountTo->setBalance($newValue, true);
            }

            $object->setCurrency($accountFromCurrency);
            $object->setMoneyOut($amount);
            $object->setMainAccount($accountFrom);
            $object->setTransferFromAccount($accountFrom);
            $object->setTransferToAccount($accountTo);
            if ($transactionType == 'Currency Exchange') {
                $object->setToCurrency($accountToCurrency);
            }

            $lastTransactionBalanceTransferFrom = $this->getLastTransactionBalance(
                $object->getDate(),
                $accountFrom,
                $object->getTransactionNumber()
            );

            $lastTransactionBalanceTransferTo = $this->getLastTransactionBalance(
                $object->getDate(),
                $accountTo,
                $object->getTransactionNumber()
            );

            $balanceTransferFrom = floatval($lastTransactionBalanceTransferFrom) - floatval($amount);

            if ($accountFromCurrency == $accountToCurrency) {
                $balanceTransferTo = floatval($lastTransactionBalanceTransferTo) + floatval($amount);
            } else {
                $balanceTransferTo = floatval($lastTransactionBalanceTransferTo) + floatval($newValue);
            }

            $object->setBalanceMainAccount(strval($balanceTransferFrom));
            $object->setBalanceTransferFromAccount(strval($balanceTransferFrom));
            $object->setBalanceTransferToAccount(strval($balanceTransferTo));

            $this->updateLaterTransactionsBalancesTransaction(
                $accountFrom,
                $object,
                $object->getDate(),
                $amount,
                false
            );

            if ($accountFromCurrency == $accountToCurrency) {
                $this->updateLaterTransactionsBalancesTransaction(
                    $accountTo,
                    $object,
                    $object->getDate(),
                    $amount,
                    true
                );
            } else {
                $this->updateLaterTransactionsBalancesTransaction(
                    $accountTo,
                    $object,
                    $object->getDate(),
                    $newValue,
                    true
                );
            }
        } elseif ($transactionType == 'Cash Withdrawal') {
            $accountFrom = $object->getTransferFromAccount();
            $accountTo = $account;
            $accountFromCurrency = $accountFrom->getCurrency();
            $accountToCurrency = $accountTo->getCurrency();
            $object->setCurrency($accountToCurrency);
            $object->setMainAccount($accountTo);
            $object->setTransferToAccount($accountTo);

            if ($accountFromCurrency !== $accountToCurrency) {
                $accountFrom->setBalance($amountFromAccount, false);
                $accountTo->setBalance($amount, true);

                $lastTransactionBalanceTransferFrom = $this->getLastTransactionBalance(
                    $object->getDate(),
                    $accountFrom,
                    $object->getTransactionNumber()
                );

                $lastTransactionBalanceTransferTo = $this->getLastTransactionBalance(
                    $object->getDate(),
                    $accountTo,
                    $object->getTransactionNumber()
                );

                $balanceTransferFrom = floatval($lastTransactionBalanceTransferFrom) - floatval($amountFromAccount);
                $balanceTransferTo = floatval($lastTransactionBalanceTransferTo) + floatval($amount);

                $this->updateLaterTransactionsBalancesTransaction(
                    $accountFrom,
                    $object,
                    $object->getDate(),
                    strval($amountFromAccount),
                    false
                );

                $this->updateLaterTransactionsBalancesTransaction(
                    $accountTo,
                    $object,
                    $object->getDate(),
                    strval($amount),
                    true
                );
            } else {
                $accountFrom->setBalance($amount, false);
                $accountTo->setBalance($amount, true);

                $lastTransactionBalanceTransferFrom = $this->getLastTransactionBalance(
                    $object->getDate(),
                    $accountFrom,
                    $object->getTransactionNumber()
                );

                $lastTransactionBalanceTransferTo = $this->getLastTransactionBalance(
                    $object->getDate(),
                    $accountTo,
                    $object->getTransactionNumber()
                );

                $balanceTransferFrom = floatval($lastTransactionBalanceTransferFrom) - floatval($amount);
                $balanceTransferTo = floatval($lastTransactionBalanceTransferTo) + floatval($amount);

                $this->updateLaterTransactionsBalancesTransaction(
                    $accountFrom,
                    $object,
                    $object->getDate(),
                    $amount,
                    false
                );

                $this->updateLaterTransactionsBalancesTransaction(
                    $accountTo,
                    $object,
                    $object->getDate(),
                    $amount,
                    true
                );
            }

            $object->setBankFeeNotApplicable(true);
            $object->setMoneyIn($amount);
            $object->setBalanceMainAccount(strval($balanceTransferTo));
            $object->setBalanceTransferFromAccount(strval($balanceTransferFrom));
            $object->setBalanceTransferToAccount(strval($balanceTransferTo));
        } elseif ($transactionType == 'Money Returned') {
            $bankPaymentTransaction = $object->getTransaction();

            $invoice = $bankPaymentTransaction->getInvoice();
            $account = $bankPaymentTransaction->getMainAccount();

            $moneyReturnedDate = $object->getDate();
            $moneyReturnedAmount = $object->getAmount();

            $account->setBalance($moneyReturnedAmount, true);

            $lastTransactionBalance = $this->getLastTransactionBalance($moneyReturnedDate, $account);
            $balance = floatval($lastTransactionBalance) + floatval($moneyReturnedAmount);

            $this->updateLaterTransactionsBalancesTransaction(
                $account,
                $object,
                $moneyReturnedDate,
                $moneyReturnedAmount,
                true
            );

            $object->setInvoice($invoice);
            $object->setMainAccount($account);
            $object->setMoneyIn($moneyReturnedAmount);
            $object->setCurrency($bankPaymentTransaction->getCurrency());
            $object->setBalanceMainAccount(strval($balance));
            $object->setBankFeeNotApplicable(true);
            $bankPaymentTransaction->setMoneyReturnedDate($moneyReturnedDate);
            $bankPaymentTransaction->setMoneyReturnedAmount($moneyReturnedAmount);

            $invoicePartPayment = $bankPaymentTransaction->getInvoicePartPayment();

            if (!empty($invoice->getInvoicePartPayments()) && $invoicePartPayment) {
                $invoice->setRestPaymentTotal($invoicePartPayment->getAmount(), true);
                $invoice->setTotalPaid($invoicePartPayment->getAmount(), false);

                if ($invoice->getRealAmountPaid() > 0) {
                    $invoice->setRealAmountPaid($invoicePartPayment->getRealAmountPaid(), false);
                }

                if ($invoicePartPayment->isBankFeeAdded()) {
                    $invoice->setBankFeeAmount($invoicePartPayment->getBankFeeAmount(), false);
                }

                if (
                    ($invoice->getTotalPaid() !== 0
                    || $invoice->getTotalPaid() == null)
                    && ($invoice->getRestPaymentTotal() != $invoice->getAmount())
                ) {
                    $invoice->setPaymentStatus('Part-Paid');
                    $invoice->setBankFeeAdded(false);
                    $invoice->setInvoiceDatePaid(null);
                } else {
                    $invoice->setPaymentStatus('Unpaid');
                    $invoice->setAccount(null);
                    $invoice->setInvoiceDatePaid(null);
                    $invoice->setBankFeeAdded(false);
                    $invoice->setBankFeeAmount(null);
                }

                $invoicePartPayment->setMoneyReturnedAmount($moneyReturnedAmount);
                $invoicePartPayment->setMoneyReturnedDate($moneyReturnedDate);
                $invoicePartPaymentRepository->add($invoicePartPayment);
            } else {
                if ($invoice->getRealAmountPaid()) {
                    $invoice->setRealAmountPaid(null);
                    $invoice->setRealCurrencyPaid(null);
                }

                $invoice->setAccount(null);
                $invoice->setInvoiceDatePaid(null);
                $invoice->setBankFeeAdded(false);
                $invoice->setBankFeeAmount(null);
                $invoice->setTotalPaid(null);
                $invoice->setRestPaymentTotal($invoice->getAmount());
                $invoice->setPaymentStatus('Unpaid');
            }

            if ($this->session()->get('moneyReturnedData')) {
                $this->session()->remove('moneyReturnedData');
            }
        } elseif ($transactionType == 'Funds transfer') {
            $date = $object->getDate();
            $bankFeeNotAdded = $object->isBankFeeNotApplicable();

            $account->setBalance($amount, true);

            $lastTransactionBalanceTransferTo = $this->getLastTransactionBalance($date, $account);
            $balanceTransferTo = floatval($lastTransactionBalanceTransferTo) + floatval($amount);

            $this->updateLaterTransactionsBalances($account, $object, $date, $amount, true);

            try {
                $this->entityManager->beginTransaction(); // Start a transaction

                $object->setCurrency($account->getCurrency());
                $object->setTransferToAccount($account);
                $object->setMoneyIn(strval($amount));
                $object->setBalanceMainAccount(strval($balanceTransferTo));
                $object->setBalanceTransferToAccount(strval($balanceTransferTo));

                $transactionRepository->add($object, true);

                $this->entityManager->commit(); // Commit the transaction
            } catch (TypeError | Exception $ex) {
                $this->errorHandler->handleDatabaseErrors($ex);

                throw $ex;
            }

            if ($bankFeeNotAdded) {
                $object->setBankFeeNotApplicable(true);
            } else {
                $bankFeeAmount = $object->getBankFeeAmount();

                if ($bankFeeAmount > 0 || $bankFeeAmount !== null) {
                    $account->setBalance($bankFeeAmount, false);

                    $lastTransactionBalanceTransferTo = $this->getLastTransactionBalance($date, $account);
                    $balanceTransferTo = floatval($lastTransactionBalanceTransferTo) - floatval($bankFeeAmount);

                    /* New record in transaction table */
                    $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                        $date,
                        $unit,
                        $bankFeeTransactionType,
                        $account,
                        $object,
                        $bankFeeAmount,
                        $userAndDateTime,
                        $balanceTransferTo,
                        'create'
                    );

                    $this->updateLaterTransactionsBalances($account, $bankFeeTransaction, $date, $bankFeeAmount, false);

                    $object->setBankFeeAdded(true);
                    $object->setBankFeeCurrency($account->getCurrency());
                }
            }
        }
    }

    // MARK: - PreUpdate
    /**
     * @throws Exception
     */
    protected function preUpdate(object $object): void
    {
        /** @var Transaction $object */

        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->entityManager->getRepository(Account::class);
        /** @var InvoicePartPaymentRepository $invoicePartPaymentRepository */
        $invoicePartPaymentRepository = $this->entityManager->getRepository(InvoicePartPayment::class);

        $userAndDateTime = $this->getUserAndDateTime();
        $bankFeeTransactionType = TransactionType::ACCOUNT_CHARGE;

        $transactionId = $object->getId();
        $transactionType = $object->getTransactionTypeName();

        /* Get current amount from the Entity & calculated difference between new and old amount */
        $currentDate = $transactionRepository->getOldDate($transactionId);
        $currentAmount = $transactionRepository->getOldAmount($transactionId);
        $currentAmountFromAccount = $transactionRepository->getOldAmountFromAccount($transactionId);
        $currentNewValue = $transactionRepository->getOldCurrentNewValue($transactionId);

        $amount = $object->getAmount();
        $amountFromAccount = $object->getAmountFromAccount();
        $newValue = $object->getNewValue();
        $differenceAmount = floatval($amount) - floatval($currentAmount);
        $differenceAmountFromAccount = floatval($amountFromAccount) - floatval($currentAmountFromAccount);
        $differenceNewValue = floatval($newValue) - floatval($currentNewValue);

        if ($transactionType == 'Cash Withdrawal') {
            $accountFrom = $object->getTransferFromAccount();
            $accountTo = $object->getTransferToAccount();
            $object->setMoneyIn($amount);

            if ($object->getDate()->format('Y-m-d') !== $currentDate->format('Y-m-d')) {
                if (
                    $object->getTransferFromAccount()->getCurrency()
                    !== $object->getTransferToAccount()->getCurrency()
                ) {
                    if ($amount != $currentAmount || $amountFromAccount != $currentAmountFromAccount) {
                        $accountFrom->setBalance(strval($differenceAmountFromAccount), false);
                        $accountTo->setBalance(strval($differenceAmount), true);

                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountFrom,
                            $object,
                            $currentDate,
                            $currentAmountFromAccount,
                            true
                        );

                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountTo,
                            $object,
                            $currentDate,
                            $currentAmount,
                            false
                        );
                    } else {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountFrom,
                            $object,
                            $currentDate,
                            $amountFromAccount,
                            true
                        );

                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountTo,
                            $object,
                            $currentDate,
                            $amount,
                            false
                        );
                    }
                } else {
                    if ($amount != $currentAmount) {
                        $accountFrom->setBalance(strval($differenceAmount), false);
                        $accountTo->setBalance(strval($differenceAmount), true);

                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountFrom,
                            $object,
                            $currentDate,
                            $currentAmount,
                            true
                        );

                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountTo,
                            $object,
                            $currentDate,
                            $currentAmount,
                            false
                        );
                    } else {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountFrom,
                            $object,
                            $currentDate,
                            $amount,
                            true
                        );

                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountTo,
                            $object,
                            $currentDate,
                            $amount,
                            false
                        );
                    }
                }

                $lastTransactionBalanceFrom = $this->getLastTransactionBalance(
                    $object->getDate(),
                    $accountFrom,
                    $object->getTransactionNumber()
                );

                $lastTransactionBalanceTo = $this->getLastTransactionBalance(
                    $object->getDate(),
                    $accountTo,
                    $object->getTransactionNumber()
                );

                if (
                    $object->getTransferFromAccount()->getCurrency()
                    !== $object->getTransferToAccount()->getCurrency()
                ) {
                    $balanceFrom = floatval($lastTransactionBalanceFrom) - floatval($amountFromAccount);
                } else {
                    $balanceFrom = floatval($lastTransactionBalanceFrom) - floatval($amount);
                }

                $balanceTo = floatval($lastTransactionBalanceTo) + floatval($amount);

                $object->setBalanceTransferFromAccount(strval($balanceFrom));
                $object->setBalanceTransferToAccount(strval($balanceTo));
                $object->setBalanceMainAccount(strval($balanceTo));

                if (
                    $object->getTransferFromAccount()->getCurrency()
                    !== $object->getTransferToAccount()->getCurrency()
                ) {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $accountFrom,
                        $object,
                        $object->getDate(),
                        $amountFromAccount,
                        false
                    );
                } else {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $accountFrom,
                        $object,
                        $object->getDate(),
                        $amount,
                        false
                    );
                }

                $this->updateLaterTransactionsBalancesTransaction(
                    $accountTo,
                    $object,
                    $object->getDate(),
                    $amount,
                    true
                );
            } else {
                if ($object->getAmountFromAccount()) {
                    $accountFrom->setBalance(strval($differenceAmountFromAccount), false);
                    $accountTo->setBalance(strval($differenceAmount), true);
                    $object->setBalanceMainAccount(strval($differenceAmount), true);
                    $object->setBalanceTransferFromAccount(strval($differenceAmountFromAccount), false);
                    $object->setBalanceTransferToAccount(strval($differenceAmount), true);

                    $this->updateLaterTransactionsBalancesTransaction(
                        $accountFrom,
                        $object,
                        $object->getDate(),
                        strval($differenceAmountFromAccount),
                        false
                    );
                } else {
                    $accountFrom->setBalance(strval($differenceAmount), false);
                    $accountTo->setBalance(strval($differenceAmount), true);
                    $object->setBalanceMainAccount(strval($differenceAmount), true);
                    $object->setBalanceTransferFromAccount(strval($differenceAmount), false);
                    $object->setBalanceTransferToAccount(strval($differenceAmount), true);

                    $this->updateLaterTransactionsBalancesTransaction(
                        $accountFrom,
                        $object,
                        $object->getDate(),
                        strval($differenceAmount),
                        false
                    );
                }

                $this->updateLaterTransactionsBalancesTransaction(
                    $accountTo,
                    $object,
                    $object->getDate(),
                    strval($differenceAmount),
                    true
                );
            }
        /* TODO: refactor simplify */
        } elseif ($transactionType == 'Funds Transfer') {
            $uow = $this->entityManager->getUnitOfWork();
            $originalTransactionData = $uow->getOriginalEntityData($object);

            $accountTo = $object->getMainAccount();
            $accountToOriginal = $originalTransactionData['mainAccount'];
            $date = $object->getDate();
            $dateOriginal = $originalTransactionData['date'];
            $bankFeeAmount = $object->getBankFeeAmount();
            $bankFeeAmountOriginal = $originalTransactionData['bankFeeAmount'];
            $bankFeeAdded = $object->isBankFeeAdded();
            $bankFeeAddedOriginal = $originalTransactionData['bankFeeAdded'];
            $bankFeeNotAdded = $object->isBankFeeNotApplicable();
            $bankFeeNotAddedOriginal = $originalTransactionData['bankFeeNotAdded'];

            $differenceBankFeeAmount = strval($bankFeeAmount - $bankFeeAmountOriginal);

            $transactions = $object->getTransactions()->toArray();
            $bankFeeTransaction = null;

            if (!empty($transactions)) {
                $bankFeeTransaction = $transactions[0];
            }

            $object->setTransferToAccount($accountTo);
            $object->setCurrency($accountTo->getCurrency());
            $object->setMoneyIn($amount);

            if ($accountTo !== $accountToOriginal) {
                if ($amount != $currentAmount) {
                    $accountToOriginal->setBalance($currentAmount, false);
                } else {
                    $accountToOriginal->setBalance($amount, false);
                }

                $accountTo->setBalance($amount, true);

                $lastTransactionBalanceAccountTo = $this->getLastTransactionBalance(
                    $object->getDate(),
                    $accountTo,
                    $object->getTransactionNumber()
                );

                $balanceAccountTo = floatval($lastTransactionBalanceAccountTo) + floatval($amount);

                $object->setBalanceMainAccount(strval($balanceAccountTo));
                $object->setBalanceTransferToAccount(strval($balanceAccountTo));

                if ($amount != $currentAmount) {
                    if ($date->format('Y-m-d') !== $dateOriginal->format('Y-m-d')) {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountToOriginal,
                            $object,
                            $dateOriginal,
                            $currentAmount,
                            false
                        );
                    } else {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountToOriginal,
                            $object,
                            $object->getDate(),
                            $currentAmount,
                            false
                        );
                    }
                } else {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $accountToOriginal,
                        $object,
                        $dateOriginal,
                        $amount,
                        false
                    );
                }

                $this->updateLaterTransactionsBalancesTransaction(
                    $accountTo,
                    $object,
                    $object->getDate(),
                    $amount,
                    true
                );

                if ($bankFeeAdded) {
                    if ($bankFeeNotAdded && !$bankFeeNotAddedOriginal) {
                        $accountToOriginal->setBalance($bankFeeAmount, true);
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountToOriginal,
                            $bankFeeTransaction,
                            $bankFeeTransaction->getDate(),
                            $bankFeeAmount,
                            true
                        );

                        $transactionRepository->remove($bankFeeTransaction, true);

                        $object->setBankFeeAdded(false);
                        $object->setBankFeeAmount('0');
                        $object->setBankFeeCurrency(null);
                    } elseif ($bankFeeAmount == 0 || $bankFeeAmount == null) {
                        $accountToOriginal->setBalance($bankFeeAmountOriginal, true);
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountToOriginal,
                            $bankFeeTransaction,
                            $bankFeeTransaction->getDate(),
                            $bankFeeAmountOriginal,
                            true
                        );

                        $transactionRepository->remove($bankFeeTransaction, true);

                        $object->setBankFeeAdded(false);
                        $object->setBankFeeAmount('0');
                        $object->setBankFeeCurrency(null);
                    } else {
                        $accountTo->setBalance($bankFeeAmount, false);

                        $object->setBankFeeCurrency($accountTo->getCurrency());

                        if (floatval($bankFeeAmount) !== floatval($bankFeeAmountOriginal)) {
                            $accountToOriginal->setBalance($bankFeeAmountOriginal, true);

                            $this->updateLaterTransactionsBalancesTransaction(
                                $accountToOriginal,
                                $bankFeeTransaction,
                                $bankFeeTransaction->getDate(),
                                $bankFeeAmountOriginal,
                                true
                            );
                        } else {
                            $accountToOriginal->setBalance($bankFeeAmount, true);

                            $this->updateLaterTransactionsBalancesTransaction(
                                $accountToOriginal,
                                $bankFeeTransaction,
                                $bankFeeTransaction->getDate(),
                                $bankFeeAmount,
                                true
                            );
                        }

                        $transactionRepository->add($object, true);

                        if ($date->format('Y-m-d') !== $dateOriginal->format('Y-m-d')) {
                            $bankFeeTransaction->setDate($date);
                        }

                        $lastTransactionBalanceAccountTo = $this->getLastTransactionBalance(
                            $bankFeeTransaction->getDate(),
                            $accountTo,
                            $bankFeeTransaction->getTransactionNumber()
                        );

                        $balanceAccountTo = floatval($lastTransactionBalanceAccountTo) - floatval($bankFeeAmount);

                        $bankFeeTransaction->setMainAccount($accountTo);
                        $bankFeeTransaction->setCurrency($accountTo->getCurrency());
                        $bankFeeTransaction->setBalanceMainAccount(strval($balanceAccountTo));

                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountTo,
                            $bankFeeTransaction,
                            $bankFeeTransaction->getDate(),
                            $bankFeeAmount,
                            false
                        );
                    }
                } elseif ($bankFeeAmount > 0 && !$bankFeeNotAdded) {
                    $date = $object->getDate();

                    try {
                        $this->entityManager->beginTransaction(); // Start a transaction

                        $accountTo->setBalance($bankFeeAmount, false);
                        $accountRepository->add($accountTo, true);

                        $lastTransactionBalanceTransferTo = $this->getLastTransactionBalance($date, $accountTo);
                        $balanceTransferTo = floatval($lastTransactionBalanceTransferTo) - floatval($bankFeeAmount);

                        /* New record in transaction table */
                        $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                            $date,
                            $object->getUnit(),
                            $bankFeeTransactionType,
                            $accountTo,
                            $object,
                            $bankFeeAmount,
                            $userAndDateTime,
                            $balanceTransferTo,
                            'create'
                        );

                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountTo,
                            $bankFeeTransaction,
                            $date,
                            $bankFeeAmount,
                            false
                        );

                        $object->setBankFeeAdded(true);
                        $object->setBankFeeCurrency($object->getCurrency());

                        $this->entityManager->commit(); // Commit the transaction
                    } catch (TypeError | Exception $ex) {
                        if ($this->entityManager->getConnection()->isTransactionActive()) {
                            $this->entityManager->rollback();
                        }

                        /* Reset Bank Fee Amount to Zero */
                        $this->entityManager->beginTransaction(); // Start a transaction

                        $object->setBankFeeAmount('0');
                        $transactionRepository->add($object, true);

                        $this->entityManager->commit(); // Commit the transaction

                        $this->errorHandler->handleDatabaseErrors($ex);

                        throw $ex;
                    }
                } elseif ($bankFeeNotAdded) {
                    $transactions = $object->getTransactions()->toArray();

                    if (!empty($transactions)) {
                        $bankFeeTransaction = $transactions[0];
                        $bankFeeAmount = $bankFeeTransaction->getAmount();

                        $accountTo->setBalance($bankFeeAmount, true);
                        $accountRepository->add($accountTo, true);

                        $this->updateLaterTransactionsBalances(
                            $accountTo,
                            $bankFeeTransaction,
                            $bankFeeTransaction->getDate(),
                            $bankFeeAmount,
                            true
                        );

                        $transactionRepository->remove($bankFeeTransaction, true);

                        $object->setBankFeeAdded(false);
                        $object->setBankFeeAmount('0');
                        $object->setBankFeeCurrency(null);
                    }
                }
            } else {
                $accountTo->setBalance(strval($differenceAmount), true);
                $accountRepository->add($accountTo, true);

                $object->setBalanceMainAccount(strval($differenceAmount), true);
                $object->setBalanceTransferToAccount(strval($differenceAmount), true);

                if ($date->format('Y-m-d') !== $dateOriginal->format('Y-m-d')) {
                    if ($amount != $currentAmount) {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountTo,
                            $object,
                            $dateOriginal,
                            $currentAmount,
                            false
                        );
                    } else {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountTo,
                            $object,
                            $dateOriginal,
                            $amount,
                            false
                        );
                    }

                    $lastTransactionBalanceAccountTo = $this->getLastTransactionBalance(
                        $object->getDate(),
                        $accountTo,
                        $object->getTransactionNumber()
                    );

                    $balanceAccountTo = floatval($lastTransactionBalanceAccountTo) + floatval($amount);

                    $object->setBalanceMainAccount(strval($balanceAccountTo));
                    $object->setBalanceTransferToAccount(strval($balanceAccountTo));
                    $transactionRepository->add($object, true);

                    $this->updateLaterTransactionsBalancesTransaction(
                        $accountTo,
                        $object,
                        $date,
                        $amount,
                        true
                    );
                } else {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $accountTo,
                        $object,
                        $date,
                        strval($differenceAmount),
                        true
                    );
                }

                if ($bankFeeAdded) {
                    if (floatval($bankFeeAmount) !== floatval($bankFeeAmountOriginal)) {
                        if (floatval($bankFeeAmount) != 0) {
                            $accountTo->setBalance($differenceBankFeeAmount, false);
                            $accountRepository->add($accountTo, true);

                            $transactionRepository->add($object, true);

                            $lastTransactionBalanceAccountTo = $object->getBalanceMainAccount();
                            $balanceAccountTo = floatval($lastTransactionBalanceAccountTo) - floatval($bankFeeAmount);

                            $bankFeeTransaction->setMoneyOut($bankFeeAmount);
                            $bankFeeTransaction->setDate($date);
                            $bankFeeTransaction->setBalanceMainAccount(strval($balanceAccountTo));

                            if ($date->format('Y-m-d') !== $dateOriginal->format('Y-m-d')) {
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $accountTo,
                                    $bankFeeTransaction,
                                    $dateOriginal,
                                    $bankFeeAmountOriginal,
                                    true
                                );
                            } else {
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $accountTo,
                                    $bankFeeTransaction,
                                    $bankFeeTransaction->getDate(),
                                    $bankFeeAmountOriginal,
                                    true
                                );
                            }

                            $this->updateLaterTransactionsBalancesTransaction(
                                $accountTo,
                                $bankFeeTransaction,
                                $bankFeeTransaction->getDate(),
                                $bankFeeAmount,
                                false
                            );
                        } else {
                            $accountTo->setBalance($bankFeeAmountOriginal, true);
                            $accountRepository->add($accountTo, true);

                            $this->updateLaterTransactionsBalances(
                                $accountTo,
                                $bankFeeTransaction,
                                $bankFeeTransaction->getDate(),
                                $bankFeeAmountOriginal,
                                true
                            );

                            $transactionRepository->remove($bankFeeTransaction, true);

                            $object->setBankFeeAdded(false);
                            $object->setBankFeeAmount('0');
                            $object->setBankFeeCurrency(null);
                        }
                    } else {
                        if ($date->format('Y-m-d') !== $dateOriginal->format('Y-m-d')) {
                            if ($bankFeeNotAdded && !$bankFeeNotAddedOriginal) {
                                $accountTo->setBalance($bankFeeAmount, true);
                                $accountRepository->add($accountTo, true);

                                $this->updateLaterTransactionsBalances(
                                    $accountTo,
                                    $bankFeeTransaction,
                                    $bankFeeTransaction->getDate(),
                                    $bankFeeAmount,
                                    true
                                );

                                $transactionRepository->remove($bankFeeTransaction, true);

                                $object->setBankFeeAdded(false);
                                $object->setBankFeeAmount('0');
                                $object->setBankFeeCurrency(null);
                            } else {
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $accountTo,
                                    $bankFeeTransaction,
                                    $dateOriginal,
                                    $bankFeeAmount,
                                    true
                                );

                                $transactionRepository->add($object, true);

                                $lastTransactionBalanceAccountTo = $object->getBalanceMainAccount();
                                $balanceAccountTo =
                                    floatval($lastTransactionBalanceAccountTo) - floatval($bankFeeAmount);

                                $bankFeeTransaction->setDate($date);
                                $bankFeeTransaction->setBalanceMainAccount(strval($balanceAccountTo));

                                $this->updateLaterTransactionsBalancesTransaction(
                                    $accountTo,
                                    $bankFeeTransaction,
                                    $bankFeeTransaction->getDate(),
                                    $bankFeeAmount,
                                    false
                                );
                            }
                        } elseif ($bankFeeNotAdded) {
                            $transactions = $object->getTransactions()->toArray();

                            if (!empty($transactions)) {
                                $bankFeeTransaction = $transactions[0];
                                $bankFeeAmount = $bankFeeTransaction->getAmount();

                                $accountTo->setBalance($bankFeeAmount, true);
                                $accountRepository->add($accountTo, true);

                                $this->updateLaterTransactionsBalances(
                                    $accountTo,
                                    $bankFeeTransaction,
                                    $bankFeeTransaction->getDate(),
                                    $bankFeeAmount,
                                    true
                                );

                                $transactionRepository->remove($bankFeeTransaction, true);

                                $object->setBankFeeAdded(false);
                                $object->setBankFeeAmount('0');
                                $object->setBankFeeCurrency(null);
                            }
                        }
                    }
                } elseif (!$bankFeeAddedOriginal) {
                    if ($bankFeeAmount > 0) {
                        $date = $object->getDate();

                        try {
                            $this->entityManager->beginTransaction(); // Start a transaction

                            $accountTo->setBalance($bankFeeAmount, false);
                            $accountRepository->add($accountTo, true);

                            $lastTransactionBalanceTransferTo = $this->getLastTransactionBalance($date, $accountTo);
                            $balanceTransferTo = floatval($lastTransactionBalanceTransferTo) - floatval($bankFeeAmount);

                            /* New record in transaction table */
                            $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                                $date,
                                $object->getUnit(),
                                $bankFeeTransactionType,
                                $accountTo,
                                $object,
                                $bankFeeAmount,
                                $userAndDateTime,
                                $balanceTransferTo,
                                'create'
                            );

                            $this->updateLaterTransactionsBalancesTransaction(
                                $accountTo,
                                $bankFeeTransaction,
                                $date,
                                $bankFeeAmount,
                                false
                            );

                            $object->setBankFeeAdded(true);
                            $object->setBankFeeCurrency($object->getCurrency());

                            $this->entityManager->commit(); // Commit the transaction
                        } catch (TypeError | Exception $ex) {
                            if ($this->entityManager->getConnection()->isTransactionActive()) {
                                $this->entityManager->rollback();
                            }

                            /* Reset Bank Fee Amount to Zero */
                            $this->entityManager->beginTransaction(); // Start a transaction

                            $object->setBankFeeAmount('0');
                            $transactionRepository->add($object, true);

                            $this->entityManager->commit(); // Commit the transaction

                            $this->errorHandler->handleDatabaseErrors($ex);

                            throw $ex;
                        }
                    }
                }
            }
        } elseif ($transactionType == 'Account Charge') {
            $account = $object->getMainAccount();
            $account->setBalance(strval($differenceAmount), false);
            $object->setMoneyOut($amount);
            $object->setBalanceMainAccount(strval($differenceAmount), false);

            if ($object->getInvoice()) {
                $invoice = $object->getInvoice();
            } else {
                $invoice = null;
            }

            if ($object->getDate()->format('Y-m-d') !== $currentDate->format('Y-m-d')) {
                if ($amount != $currentAmount) {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $object,
                        $currentDate,
                        $currentAmount,
                        true
                    );
                } else {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $object,
                        $currentDate,
                        $amount,
                        true
                    );
                }

                $lastTransactionBalance = $this->getLastTransactionBalance(
                    $object->getDate(),
                    $account,
                    $object->getTransactionNumber()
                );

                $balance = floatval($lastTransactionBalance) - floatval($amount);
                $object->setBalanceMainAccount(strval($balance));

                $this->updateLaterTransactionsBalancesTransaction(
                    $account,
                    $object,
                    $object->getDate(),
                    $amount,
                    false
                );
            } else {
                $this->updateLaterTransactionsBalancesTransaction(
                    $account,
                    $object,
                    $object->getDate(),
                    strval($differenceAmount),
                    false
                );
            }

            if ($object->getInvoice()) {
                if ($object->getTransaction()) {
                    if ($object->getTransaction()->getInvoicePartPayment()) {
                        $partPayment = $object->getTransaction()->getInvoicePartPayment();

                        $partPayment->setBankFeeAmount($amount);
                        $invoice->setBankFeeAmount(strval($differenceAmount), true);
                    }
                } else {
                    $invoice->setBankFeeAmount($amount);
                }
            }
        } elseif ($transactionType == 'Money Returned') {
            $account = $object->getMainAccount();
            $account->setBalance(strval($differenceAmount), true);
            $object->setMoneyIn($amount);
            $object->setBalanceMainAccount(strval($differenceAmount), true);

            if ($object->getDate()->format('Y-m-d') !== $currentDate->format('Y-m-d')) {
                if ($amount != $currentAmount) {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $object,
                        $currentDate,
                        $currentAmount,
                        false
                    );
                } else {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $object,
                        $currentDate,
                        $amount,
                        false
                    );
                }

                $lastTransactionBalance = $this->getLastTransactionBalance(
                    $object->getDate(),
                    $account,
                    $object->getTransactionNumber()
                );

                $balance = floatval($lastTransactionBalance) + floatval($amount);
                $object->setBalanceMainAccount(strval($balance));
                $this->updateLaterTransactionsBalancesTransaction(
                    $account,
                    $object,
                    $object->getDate(),
                    $amount,
                    true
                );
            } else {
                if ($amount != $currentAmount) {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $object,
                        $object->getDate(),
                        $currentAmount,
                        false
                    );

                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $object,
                        $object->getDate(),
                        $amount,
                        true
                    );
                }
            }
        } elseif (
            $transactionType == 'Bank Transfer'
            || $transactionType == 'Currency Exchange'
            || $transactionType == 'Cash Transfer'
        ) {
            $accountTo = $object->getTransferToAccount();

            if ($transactionType == 'Cash Transfer' && !($object->getTransferFromAccount())) {
                $accountTo->setBalance(strval($differenceAmount), true);

                $object->setMoneyIn($amount);
                $object->setBalanceMainAccount(strval($differenceAmount), true);
                $object->setBalanceTransferToAccount(strval($differenceAmount), true);

                if ($object->getDate()->format('Y-m-d') !== $currentDate->format('Y-m-d')) {
                    if ($amount != $currentAmount) {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountTo,
                            $object,
                            $currentDate,
                            $currentAmount,
                            false
                        );
                    } else {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountTo,
                            $object,
                            $currentDate,
                            $amount,
                            false
                        );
                    }

                    $lastTransactionBalanceAccountTo = $this->getLastTransactionBalance(
                        $object->getDate(),
                        $accountTo,
                        $object->getTransactionNumber()
                    );

                    $balanceAccountTo = floatval($lastTransactionBalanceAccountTo) + floatval($amount);

                    $object->setBalanceMainAccount(strval($balanceAccountTo));
                    $object->setBalanceTransferToAccount(strval($balanceAccountTo));

                    $this->updateLaterTransactionsBalancesTransaction(
                        $accountTo,
                        $object,
                        $object->getDate(),
                        $amount,
                        true
                    );
                } else {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $accountTo,
                        $object,
                        $object->getDate(),
                        strval($differenceAmount),
                        true
                    );
                }
            } else {
                $uow = $this->entityManager->getUnitOfWork();
                $originalTransactionData = $uow->getOriginalEntityData($object);
                $accountToPrevious = $originalTransactionData['transferToAccount'];

                $accountFrom = $object->getTransferFromAccount();
                $accountFrom->setBalance(strval($differenceAmount), false);
                $accountFromCurrency = $accountFrom->getCurrency();
                $accountToCurrency = $accountTo->getCurrency();
                $accountToPreviousCurrency = $accountToPrevious->getCurrency();

                if ($accountTo !== $accountToPrevious) {
                    if ($amount != $currentAmount) {
                        if ($accountFromCurrency == $accountToPreviousCurrency) {
                            $accountToPrevious->setBalance($currentAmount, false);
                        } else {
                            $accountToPrevious->setBalance($currentNewValue, false);
                        }
                    } else {
                        if ($accountFromCurrency == $accountToPreviousCurrency) {
                            $accountToPrevious->setBalance($amount, false);
                        } else {
                            $accountToPrevious->setBalance($currentNewValue, false);
                        }
                    }

                    if ($accountFromCurrency == $accountToCurrency) {
                        $accountTo->setBalance($amount, true);
                    } else {
                        $accountTo->setBalance($newValue, true);
                    }
                } else {
                    if ($accountFromCurrency == $accountToCurrency) {
                        $accountTo->setBalance(strval($differenceAmount), true);
                    } else {
                        $accountTo->setBalance(strval($differenceNewValue), true);
                    }
                }

                $object->setMoneyOut($amount);

                if ($transactionType == 'Currency exchange') {
                    $object->setToCurrency($accountToCurrency);
                }

                if (
                    $object->getTransferFromAccount()->getAccountTypeName() == 'Bank Account'
                    && (!$object->getTransferToAccount()->getAccountTypeName() == 'Bank Account'
                    || !$object->getTransferToAccount()->getAccountTypeName() == 'Card Account')
                ) {
                    $object->setBalanceMainAccount(strval($differenceAmount), true);
                    $object->setBalanceTransferFromAccount(strval($differenceAmount), false);
                    $object->setBalanceTransferToAccount(strval($differenceAmount), true);
                } else {
                    $object->setBalanceMainAccount(strval($differenceAmount), false);
                    $object->setBalanceTransferFromAccount(strval($differenceAmount), false);

                    if ($accountTo !== $accountToPrevious) {
                        $lastTransactionBalanceAccountTo = $this->getLastTransactionBalance(
                            $object->getDate(),
                            $accountTo,
                            $object->getTransactionNumber()
                        );

                        if ($accountFromCurrency == $accountToCurrency) {
                            $balanceAccountTo = floatval($lastTransactionBalanceAccountTo) + floatval($amount);
                        } else {
                            $balanceAccountTo = floatval($lastTransactionBalanceAccountTo) + floatval($newValue);
                        }

                        $object->setBalanceTransferToAccount(strval($balanceAccountTo));
                    } else {
                        if ($accountFromCurrency == $accountToCurrency) {
                            $object->setBalanceTransferToAccount(strval($differenceAmount), true);
                        } else {
                            $object->setBalanceTransferToAccount(strval($differenceNewValue), true);
                        }
                    }
                }

                if ($object->getDate()->format('Y-m-d') !== $currentDate->format('Y-m-d')) {
                    if ($amount != $currentAmount) {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountFrom,
                            $object,
                            $currentDate,
                            $currentAmount,
                            true
                        );

                        if ($accountTo !== $accountToPrevious) {
                            if ($accountFromCurrency == $accountToCurrency) {
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $accountToPrevious,
                                    $object,
                                    $currentDate,
                                    $currentAmount,
                                    false
                                );
                            } else {
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $accountToPrevious,
                                    $object,
                                    $currentDate,
                                    $currentNewValue,
                                    false
                                );
                            }

                            if ($object->isBankFeeAdded()) {
                                $bankFeeAmount = $object->getBankFeeAmount();
                                $bankFeeTransaction = $object->getTransactions()->toArray()[0];

                                $accountTo->setBalance($bankFeeAmount, true);
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $accountTo,
                                    $bankFeeTransaction,
                                    $currentDate,
                                    $bankFeeAmount,
                                    true
                                );
                            }
                        } else {
                            if ($accountFromCurrency == $accountToCurrency) {
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $accountTo,
                                    $object,
                                    $currentDate,
                                    $currentAmount,
                                    false
                                );
                            } else {
                                if ($newValue !== $currentNewValue) {
                                    $this->updateLaterTransactionsBalancesTransaction(
                                        $accountTo,
                                        $object,
                                        $currentDate,
                                        $currentNewValue,
                                        false
                                    );
                                } else {
                                    $this->updateLaterTransactionsBalancesTransaction(
                                        $accountTo,
                                        $object,
                                        $currentDate,
                                        $newValue,
                                        false
                                    );
                                }
                            }
                        }
                    } else {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountFrom,
                            $object,
                            $currentDate,
                            $amount,
                            true
                        );

                        if ($accountTo !== $accountToPrevious) {
                            if ($accountFromCurrency == $accountToPreviousCurrency) {
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $accountToPrevious,
                                    $object,
                                    $currentDate,
                                    $currentAmount,
                                    false
                                );
                            } else {
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $accountToPrevious,
                                    $object,
                                    $currentDate,
                                    $currentNewValue,
                                    false
                                );
                            }
                        } else {
                            if ($accountFromCurrency == $accountToCurrency) {
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $accountTo,
                                    $object,
                                    $currentDate,
                                    $amount,
                                    false
                                );
                            } else {
                                if ($newValue !== $currentNewValue) {
                                    $this->updateLaterTransactionsBalancesTransaction(
                                        $accountTo,
                                        $object,
                                        $currentDate,
                                        $currentNewValue,
                                        false
                                    );
                                } else {
                                    $this->updateLaterTransactionsBalancesTransaction(
                                        $accountTo,
                                        $object,
                                        $currentDate,
                                        $newValue,
                                        false
                                    );
                                }
                            }
                        }
                    }

                    $lastTransactionBalanceAccountFrom = $this->getLastTransactionBalance(
                        $object->getDate(),
                        $accountFrom,
                        $object->getTransactionNumber()
                    );

                    $lastTransactionBalanceAccountTo = $this->getLastTransactionBalance(
                        $object->getDate(),
                        $accountTo,
                        $object->getTransactionNumber()
                    );

                    $balanceAccountFrom = floatval($lastTransactionBalanceAccountFrom) - floatval($amount);

                    if ($accountFromCurrency == $accountToCurrency) {
                        $balanceAccountTo = floatval($lastTransactionBalanceAccountTo) + floatval($amount);
                    } else {
                        $balanceAccountTo = floatval($lastTransactionBalanceAccountTo) + floatval($newValue);
                    }

                    $object->setBalanceMainAccount(strval($balanceAccountFrom));

                    $object->setBalanceTransferFromAccount(strval($balanceAccountFrom));
                    $object->setBalanceTransferToAccount(strval($balanceAccountTo));

                    $this->updateLaterTransactionsBalancesTransaction(
                        $accountFrom,
                        $object,
                        $object->getDate(),
                        $amount,
                        false
                    );

                    if ($accountFromCurrency == $accountToCurrency) {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountTo,
                            $object,
                            $object->getDate(),
                            $amount,
                            true
                        );
                    } else {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountTo,
                            $object,
                            $object->getDate(),
                            $newValue,
                            true
                        );
                    }
                } else {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $accountFrom,
                        $object,
                        $object->getDate(),
                        strval($differenceAmount),
                        false
                    );

                    if ($accountTo !== $accountToPrevious) {
                        if ($accountFromCurrency == $accountToPreviousCurrency) {
                            $this->updateLaterTransactionsBalancesTransaction(
                                $accountToPrevious,
                                $object,
                                $object->getDate(),
                                $currentAmount,
                                false
                            );
                        } else {
                            $this->updateLaterTransactionsBalancesTransaction(
                                $accountToPrevious,
                                $object,
                                $object->getDate(),
                                $currentNewValue,
                                false
                            );
                        }

                        if ($accountFromCurrency == $accountToCurrency) {
                            $this->updateLaterTransactionsBalancesTransaction(
                                $accountTo,
                                $object,
                                $object->getDate(),
                                $amount,
                                true
                            );
                        } else {
                            $this->updateLaterTransactionsBalancesTransaction(
                                $accountTo,
                                $object,
                                $object->getDate(),
                                $newValue,
                                true
                            );
                        }
                    } else {
                        if ($accountFromCurrency == $accountToCurrency) {
                            $this->updateLaterTransactionsBalancesTransaction(
                                $accountTo,
                                $object,
                                $object->getDate(),
                                strval($differenceAmount),
                                true
                            );
                        } else {
                            $this->updateLaterTransactionsBalancesTransaction(
                                $accountTo,
                                $object,
                                $object->getDate(),
                                strval($differenceNewValue),
                                true
                            );
                        }
                    }
                }
            }
        } elseif ($transactionType == 'Bank Payment') {
            if ($object->getInvoicePartPayment()) {
                $invoice = $object->getInvoice();
                $account = $object->getMainAccount();
                $partPayment = $object->getInvoicePartPayment();

                if ($object->getDate()->format('Y-m-d') !== $currentDate->format('Y-m-d')) {
                    if ($amount != $currentAmount) {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $object,
                            $currentDate,
                            $currentAmount,
                            true
                        );

                        $account->setBalance($currentAmount, true);

                        $lastTransactionBalance = $this->getLastTransactionBalance(
                            $object->getDate(),
                            $account,
                            $object->getTransactionNumber()
                        );

                        $balance = floatval($lastTransactionBalance) - floatval($amount);

                        $object->setBalanceMainAccount(strval($balance));
                        $object->setMoneyOut($amount);

                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $object,
                            $object->getDate(),
                            $amount,
                            false
                        );

                        $account->setBalance($amount, false);

                        $partPayment->setDatePaid($object->getDate());
                        $partPayment->setAmount($amount);
                        $partPayment->setRestPaymentAmount(strval($differenceAmount), false);
                        $invoice->setRestPaymentTotal(strval($differenceAmount), false);
                        $invoice->setTotalPaid(strval($differenceAmount), true);
                    } else {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $object,
                            $currentDate,
                            $amount,
                            true
                        );

                        $lastTransactionBalance = $this->getLastTransactionBalance(
                            $object->getDate(),
                            $account,
                            $object->getTransactionNumber()
                        );

                        $balance = floatval($lastTransactionBalance) - floatval($amount);

                        $object->setBalanceMainAccount(strval($balance));

                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $object,
                            $object->getDate(),
                            $amount,
                            false
                        );

                        $partPayment->setDatePaid($object->getDate());
                    }
                } elseif ($amount != $currentAmount) {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $object,
                        $object->getDate(),
                        $currentAmount,
                        true
                    );

                    $account->setBalance($currentAmount, true);

                    $lastTransactionBalance = $this->getLastTransactionBalance(
                        $object->getDate(),
                        $account,
                        $object->getTransactionNumber()
                    );

                    $balance = floatval($lastTransactionBalance) - floatval($amount);

                    $object->setBalanceMainAccount(strval($balance));
                    $object->setMoneyOut($amount);

                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $object,
                        $object->getDate(),
                        $amount,
                        false
                    );

                    $account->setBalance($amount, false);

                    $partPayment->setAmount($amount);
                    $partPayment->setRestPaymentAmount(strval($differenceAmount), false);
                    $invoice->setRestPaymentTotal(strval($differenceAmount), false);
                    $invoice->setTotalPaid(strval($differenceAmount), true);
                }

                if ($invoice->getTotalPaid() == $invoice->getAmount()) {
                    $invoicePartPayments = $invoicePartPaymentRepository->findBy(['invoice' => $invoice]);
                    $allBankFeeAdded = true;

                    foreach ($invoicePartPayments as $partPayment) {
                        if (!$partPayment->isBankFeeAdded()) {
                            $allBankFeeAdded = false;

                            break;
                        }
                    }

                    $invoice->setPaymentStatus('Paid');
                    $invoice->setInvoiceDatePaid($object->getDate());

                    if ($allBankFeeAdded) {
                        $invoice->setBankFeeAdded(true);
                    }
                } else {
                    $invoice->setPaymentStatus('Part-Paid');
                    $invoice->setInvoiceDatePaid(null);
                    $invoice->setBankFeeAdded(false);
                }

                // if ($invoice->getTotalPaid() > $invoice->getAmount()) {
                    /* TODO: Handle this exception */
                // }
            }
        } else {
            $accountFrom = $object->getTransferFromAccount();
            $accountTo = $object->getTransferToAccount();
            $accountToOld = $this->getAccountTo($transactionId);
            $object->setMoneyOut($amount);

            if ($accountTo !== $accountToOld) {
                $accountTo->setBalance($amount, true);
                $accountToOld->setBalance($currentAmount, false);
                $accountFrom->setBalance(strval($differenceAmount), false);
                $object->setBalanceMainAccount($accountFrom->getBalance());
            } else {
                $accountFrom->setBalance(strval($differenceAmount), false);
                $accountTo->setBalance(strval($differenceAmount), true);
                $object->setBalanceTransferFromAccount(strval($differenceAmount), false);
                $object->setBalanceTransferToAccount(strval($differenceAmount), true);
                $object->setBalanceMainAccount(strval($differenceAmount), false);

                $this->updateLaterTransactionsBalancesTransaction(
                    $accountFrom,
                    $object,
                    $object->getDate(),
                    strval($differenceAmount),
                    false
                );

                $this->updateLaterTransactionsBalancesTransaction(
                    $accountTo,
                    $object,
                    $object->getDate(),
                    strval($differenceAmount),
                    true
                );
            }
        }

        /* TODO: add editedByUser in transaction entity */
        // $transaction->setEditedByUser($user);
        $object->setDateTimeEdited(new DateTime());
    }

    // MARK: - PreRemove
    protected function preRemove(object $object): void
    {
        /** @var Transaction $object */

        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);
        /** @var InvoicePartPaymentRepository $invoicePartPaymentRepository */
        $invoicePartPaymentRepository = $this->entityManager->getRepository(InvoicePartPayment::class);

        /* Get data from the object */
        $transactionType = $object->getTransactionTypeName();
        $accountFrom = $object->getTransferFromAccount();
        $accountTo = $object->getTransferToAccount();
        $amount = $object->getAmount();
        $newValue = $object->getNewValue();
        $realAmount = $object->getRealAmountPaid();
        $amountFromAccount = $object->getAmountFromAccount();

        if ($transactionType == 'Cash Withdrawal') {
            if (
                $object->getTransferFromAccount()->getCurrency()
                !== $object->getTransferToAccount()->getCurrency()
            ) {
                $accountFrom->setBalance($amountFromAccount, true);
                $accountTo->setBalance($amount, false);

                $this->updateLaterTransactionsBalancesTransaction(
                    $accountFrom,
                    $object,
                    $object->getDate(),
                    $amountFromAccount,
                    true
                );
            } else {
                $accountFrom->setBalance($amount, true);
                $accountTo->setBalance($amount, false);

                $this->updateLaterTransactionsBalancesTransaction(
                    $accountFrom,
                    $object,
                    $object->getDate(),
                    $amount,
                    true
                );
            }

            $this->updateLaterTransactionsBalancesTransaction(
                $accountTo,
                $object,
                $object->getDate(),
                $amount,
                false
            );

            if ($object->isBankFeeAdded()) {
                $accountFrom->setBalance($object->getBankFeeAmount(), true);

                $bankFeeTransaction = $object->getTransactions()->toArray()[0];

                $this->updateLaterTransactionsBalancesTransaction(
                    $accountFrom,
                    $bankFeeTransaction,
                    $object->getDate(),
                    $object->getBankFeeAmount(),
                    true
                );
            }
        } elseif ($transactionType == 'Bank Transfer' && !$accountFrom) {
            $accountTo->setBalance($amount, false);
        } elseif (
            $transactionType == 'Bank transfer'
            || $transactionType == 'Currency exchange'
            || $transactionType == 'Cash transfer'
        ) {
            $accountToCurrency = $accountTo->getCurrency();

            if ($accountFrom) {
                $accountFromCurrency = $accountFrom->getCurrency();

                if ($accountFromCurrency == $accountToCurrency) {
                    $accountFrom->setBalance($amount, true);
                    $accountTo->setBalance($amount, false);
                } else {
                    if ($realAmount) {
                        $accountFrom->setBalance($realAmount, true);
                        $accountTo->setBalance($amount, false);
                    } elseif ($newValue) {
                        $accountFrom->setBalance($amount, true);
                        $accountTo->setBalance($newValue, false);
                    }
                }
            } else {
                $accountTo->setBalance($amount, false);
            }

            if ($object->isBankFeeAdded()) {
                $bankFeeAmount = $object->getBankFeeAmount();
                $bankFeeTransaction = $object->getTransactions()->toArray()[0];

                $bankFeeMainAccount = $bankFeeTransaction->getMainAccount();

                $bankFeeMainAccount->setBalance($bankFeeAmount, true);
                $this->updateLaterTransactionsBalancesTransaction(
                    $bankFeeMainAccount,
                    $bankFeeTransaction,
                    $bankFeeTransaction->getDate(),
                    $bankFeeAmount,
                    true
                );

                $transactionRepository->remove($bankFeeTransaction, true);
            }

            if ($accountFrom) {
                $this->updateLaterTransactionsBalancesTransaction(
                    $accountFrom,
                    $object,
                    $object->getDate(),
                    $amount,
                    true
                );

                if ($accountFromCurrency == $accountToCurrency) {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $accountTo,
                        $object,
                        $object->getDate(),
                        $amount,
                        false
                    );
                } else {
                    if ($realAmount) {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountTo,
                            $object,
                            $object->getDate(),
                            $amount,
                            false
                        );
                    } elseif ($newValue) {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $accountTo,
                            $object,
                            $object->getDate(),
                            $newValue,
                            false
                        );
                    }
                }
            } else {
                $this->updateLaterTransactionsBalancesTransaction(
                    $accountTo,
                    $object,
                    $object->getDate(),
                    $amount,
                    false
                );
            }
        } elseif ($transactionType == 'Account Charge' && $object->getInvoice()) {
            $invoice = $object->getInvoice();

            $mainAccount = $object->getMainAccount();
            $mainAccount->setBalance($amount, true);
            $connectedTransaction = $object->getTransaction();

            if ($connectedTransaction) {
                if ($connectedTransaction->getInvoicePartPayment()) {
                    $connectedTransaction->setBankFeeAdded(false);
                    $connectedTransaction->setBankFeeCurrency(null);
                    $connectedTransaction->setBankFeeAmount('0');

                    $invoice->setBankFeeAmount($amount, false);

                    $partPayment = $connectedTransaction->getInvoicePartPayment();
                    $partPayment->setBankFeeAmount(null);
                } else {
                    $connectedTransaction->setBankFeeAdded(false);
                    $connectedTransaction->setBankFeeCurrency(null);
                    $connectedTransaction->setBankFeeAmount('0');

                    $invoice->setBankFeeAdded(false);
                    $invoice->setBankFeeAmount('0');
                }
            } else {
                $invoice->setBankFeeAdded(false);
                $invoice->setBankFeeAmount('0');
            }

            $this->updateLaterTransactionsBalancesTransaction(
                $mainAccount,
                $object,
                $object->getDate(),
                $amount,
                true
            );
        } elseif ($transactionType == 'Account Charge' && $object->getTransaction()) {
            $account = $object->getMainAccount();
            $account->setBalance($amount, true);
            $relatedTransaction = $object->getTransaction();
            $relatedTransaction->setBankFeeAdded(false);
            $relatedTransaction->setBankFeeAmount('0');

            $this->updateLaterTransactionsBalancesTransaction(
                $account,
                $object,
                $object->getDate(),
                $amount,
                true
            );
        } elseif ($transactionType == 'Funds Transfer') {
            $account = $object->getMainAccount();

            if ($object->isBankFeeAdded()) {
                $amount = $object->getAmount();

                $account->setBalance($amount, false);

                $this->updateLaterTransactionsBalancesTransaction(
                    $account,
                    $object,
                    $object->getDate(),
                    $amount,
                    false
                );

                $transactions = $object->getTransactions()->toArray();

                if (!empty($transactions)) {
                    $bankFeeTransaction = $transactions[0];

                    $account->setBalance($bankFeeTransaction->getAmount(), true);
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $bankFeeTransaction,
                        $object->getDate(),
                        $bankFeeTransaction->getAmount(),
                        true
                    );

                    $transactionRepository->remove($bankFeeTransaction);
                }
            } else {
                /* Return the amount to the account */
                $amount = $object->getAmount();
                $account->setBalance($amount, false);
                $this->updateLaterTransactionsBalancesTransaction(
                    $account,
                    $object,
                    $object->getDate(),
                    $amount,
                    false
                );
            }
        } elseif ($transactionType == 'Bank Payment') {
            $mainAccount = $object->getMainAccount();
            $invoice = $object->getInvoice();
            $realAmountPaid = $invoice->getRealAmountPaid();
            $invoiceCurrency = $invoice->getCurrency();
            $accountCurrency = $mainAccount->getCurrency();
            $bankFeeAmount = $object->getBankFeeAmount();
            $moneyReturnedAmount = $object->getMoneyReturnedAmount();

            $invoicePartPayment = $object->getInvoicePartPayment();

            $connectedTransactions = $object->getTransactions()->toArray();

            $moneyReturnedTransaction = null;
            $bankFeeTransaction = null;

            foreach ($connectedTransactions as $item) {
                if ($item->getTransactionType() == 'Money Returned') {
                    $moneyReturnedTransaction = $item;
                } elseif ($item->getTransactionType() == 'Account Charge') {
                    $bankFeeTransaction = $item;
                }
            }

            if ($moneyReturnedTransaction && $moneyReturnedAmount !== null) {
                $mainAccount->setBalance($moneyReturnedAmount, false);

                $this->updateLaterTransactionsBalancesTransaction(
                    $mainAccount,
                    $moneyReturnedTransaction,
                    $moneyReturnedTransaction->getDate(),
                    $moneyReturnedAmount,
                    false
                );

                $transactionRepository->remove($moneyReturnedTransaction, true);
            }

            if ($bankFeeTransaction) {
                $mainAccount->setBalance($bankFeeAmount, true);

                $this->updateLaterTransactionsBalancesTransaction(
                    $mainAccount,
                    $bankFeeTransaction,
                    $bankFeeTransaction->getDate(),
                    $bankFeeAmount,
                    true
                );

                $transactionRepository->remove($bankFeeTransaction, true);
            }

            $mainAccount->setBalance($amount, true);

            $this->updateLaterTransactionsBalancesTransaction(
                $mainAccount,
                $object,
                $object->getDate(),
                $amount,
                true
            );

            if ($invoicePartPayment) {
                if (!$moneyReturnedTransaction) {
                    $invoice->setBankFeeAmount($invoicePartPayment->getBankFeeAmount(), false);
                    $invoice->setRestPaymentTotal($invoicePartPayment->getAmount(), true);
                    $invoice->setTotalPaid($invoicePartPayment->getAmount(), false);

                    if ($invoicePartPayment->getRealAmountPaid()) {
                        $invoice->setRealAmountPaid($invoicePartPayment->getRealAmountPaid(), false);
                    }
                }

                $invoicePartPaymentRepository->remove($invoicePartPayment, true);

                $partPayments = $invoicePartPaymentRepository->findBy(['invoice' => $invoice->getId()]);

                if (count($partPayments) == 0) {
                    $invoice->setPaymentStatus('Unpaid');
                    $invoice->setAccount(null);

                    if ($invoice->getRealCurrencyPaid()) {
                        $invoice->setRealAmountPaid(null);
                        $invoice->setRealCurrencyPaid(null);
                    }
                } else {
                    $invoice->setPaymentStatus('Part-Paid');
                }
            } else {
                if (($invoiceCurrency !== $accountCurrency) && $realAmountPaid) {
                    $invoice->setRealAmountPaid(null);
                    $invoice->setRealCurrencyPaid(null);
                }

                $invoice->setAccount(null);
                $invoice->setInvoiceDatePaid(null);
                $invoice->setPaymentStatus('Unpaid');
                $invoice->setBankFeeAdded(false);
                $invoice->setBankFeeAmount('0');
                $invoice->setRestPaymentTotal($invoice->getAmount());
                $invoice->setTotalPaid('0');
            }
        } elseif ($transactionType == 'Card Payment' || $transactionType == 'Cash Payment') {
            $mainAccount = $object->getMainAccount();
            $mainAccount->setBalance($amount, true);

            $this->updateLaterTransactionsBalancesTransaction(
                $mainAccount,
                $object,
                $object->getDate(),
                $amount,
                true
            );
        } elseif ($transactionType == 'Money Returned') {
            $moneyReturnedAmount = $object->getAmount();
            $bankPaymentTransaction = $object->getTransaction();
            $invoice = $bankPaymentTransaction->getInvoice();
            $account = $bankPaymentTransaction->getMainAccount();
            $invoiceCurrency = $invoice->getCurrency();
            $bankPaymentTransactionCurrency = $bankPaymentTransaction->getCurrency();

            $invoicePartPayment = $bankPaymentTransaction->getInvoicePartPayment();

            $account->setBalance($moneyReturnedAmount, false);

            $this->updateLaterTransactionsBalancesTransaction(
                $account,
                $object,
                $object->getDate(),
                $moneyReturnedAmount,
                false
            );

            $bankPaymentTransaction->setMoneyReturnedAmount(null);
            $bankPaymentTransaction->setMoneyReturnedDate(null);

            $invoice->setAccount($bankPaymentTransaction->getMainAccount());

            if ($invoicePartPayment) {
                $invoice->setBankFeeAmount($invoicePartPayment->getBankFeeAmount(), true);
                $invoice->setRestPaymentTotal($invoicePartPayment->getAmount(), false);
                $invoice->setTotalPaid($invoicePartPayment->getAmount(), true);

                if ($invoicePartPayment->getRealAmountPaid()) {
                    $invoice->setRealAmountPaid($invoicePartPayment->getRealAmountPaid(), true);
                }

                $invoicePartPayment->setMoneyReturnedAmount(null);
                $invoicePartPayment->setMoneyReturnedDate(null);

                $invoiceTotalPaid = $invoice->getTotalPaid();
                $invoiceRestPayment = $invoice->getRestPaymentTotal();

                if (($invoiceTotalPaid == $invoice->getAmount()) && $invoiceRestPayment == 0) {
                    $invoice->setPaymentStatus('Paid');
                    $invoice->setInvoiceDatePaid($bankPaymentTransaction->getDate());

                    $invoicePartPayments = $invoice->getInvoicePartPayments()->toArray();

                    /* Check if Bank Fee is added to existing Part Payment in Invoice Edit */
                    $allBankFeesAddedInPartPayments = true;

                    foreach ($invoicePartPayments as $partPayment) {
                        if (!$partPayment->isBankFeeAdded() && !$partPayment->isBankFeeNotAppplicable()) {
                            $allBankFeesAddedInPartPayments = false;
                        }
                    }

                    if ($allBankFeesAddedInPartPayments) {
                        $invoice->setBankFeeAdded(true);
                    }
                } else {
                    $invoice->setPaymentStatus('Part-Paid');
                }
            } else {
                $invoice->setPaymentStatus('Paid');
                $invoice->setInvoiceDatePaid($bankPaymentTransaction->getDate());

                if ($invoiceCurrency !== $bankPaymentTransactionCurrency) {
                    $invoice->setRealAmountPaid($bankPaymentTransaction->getAmount());
                    $invoice->setRealCurrencyPaid($bankPaymentTransactionCurrency);
                }

                if ($bankPaymentTransaction->isBankFeeAdded()) {
                    $invoice->setBankFeeAdded(true);
                    $invoice->setBankFeeAmount($bankPaymentTransaction->getBankFeeAmount());
                }
            }
        } else {
            $accountFrom->setBalance($amount, true);
            $accountTo->setBalance($amount, false);
        }
    }

    // MARK: - Configure Query
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        if (!$query instanceof ProxyQuery) {
            throw new InvalidArgumentException('Expected an instance of ProxyQuery');
        }

        $qb = $query->getQueryBuilder();
        $rootAlias = current($query->getQueryBuilder()->getRootAliases());


        // /* Get unit */
        // $unitId = $this->getUnitId();

        // if ($unitId) {
        //     $qb
        //         ->where($rootAlias . '.unit = :unitId')
        //         ->orderBy($rootAlias . '.date', 'DESC')
        //         ->addOrderBy($rootAlias . '.transactionNumber', 'DESC')
        //         ->setParameter('unitId', $unitId)
        //     ;
        // } else {
        //     $qb
        //         ->join($rootAlias . '.unit', 'u')
        //         ->andWhere('u.active = :active')
        //         ->orderBy($rootAlias . '.date', 'DESC')
        //         ->addOrderBy($rootAlias . '.transactionNumber', 'DESC')
        //         ->setParameter('active', true)
        //     ;
        // }

        return $query;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::SORT_BY] = 'date';
    }

    private function getAccountTo(int $transactionId): ?Account
    {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        return $transactionRepository->getTransferToAccount($transactionId);
    }

    private function getMainAccountIds(int $unitId = null): ?array
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->entityManager->getRepository(Account::class);

        if ($unitId) {
            $mainAccounts = $accountRepository->findBy(['unit' => $unitId]);
        } else {
            $mainAccounts = $accountRepository->findAll();
        }

        $mainAccountIds = [];

        foreach ($mainAccounts as $mainAccount) {
            $mainAccountIds[] = $mainAccount->getId();
        }

        return $mainAccountIds;
    }
}
