<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Account;
use App\Entity\Budget;
use App\Entity\Department;
use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Entity\InvoicePartPayment;
use App\Entity\Supplier;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Entity\Unit;
use App\Form\Type\CustomButtonType;
use App\Form\Type\EmptyType;
use App\Repository\AccountRepository;
use App\Repository\InvoiceRepository;
use App\Repository\InvoiceLineRepository;
use App\Repository\InvoicePartPaymentRepository;
use App\Repository\TransactionRepository;
use App\Repository\UnitRepository;
use App\Service\BudgetCalculationService;
use App\Service\ErrorHandler;
use App\Service\TransactionService;
use App\Traits\AdminTrait;
use App\Validator\Constraints\InvoicePartPaymentsTotal;
use App\Validator\Constraints\LineNetTotalDifference;
use App\Validator\Constraints\NoInvoiceLineAdded;
use App\Validator\Constraints\PartPaymentValue;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use InvalidArgumentException;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\CollectionType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InvoiceAdmin extends AbstractAdmin
{
    use AdminTrait;

    public function __construct(
        private ErrorHandler $errorHandler,
        private TransactionService $transactionService,
        private BudgetCalculationService $budgetCalculationService,
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage
    ) {
        parent::__construct(null, Invoice::class, null);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->add('comments', $this->getRouterIdParameter() . '/comments')
            // ->add('viewDocument', $this->getRouterIdParameter().'/viewDocument')
            ->add('addBankFeeLinkToModal', $this->getRouterIdParameter() . '/addBankFeeLinkToModal')
            ->add('addBankFee', $this->getRouterIdParameter() . '/addBankFee')
            // ->add('redirectToMoneyReturn', $this->getRouterIdParameter().'/money_return')
        ;
    }

    /* Remove batch delete action from the list */
    protected function configureBatchActions($actions): array
    {
        unset($actions['delete']);

        return $actions;
    }

    /* Remove Download button from bottom of the list */
    public function getExportFormats(): array
    {
        return [];
    }

    // MARK: - Datagrid Filters
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        // /* Get unit */
        // $unitId = $this->getUnitId();

        $accountIds = [];

        // if ($unitId) {
        //     $accountIds = $this->getInvoiceAccountIds(strval($unitId));
        // }

        $filter
            ->add('invoiceNumber')
            ->add('invoiceDate', DateRangeFilter::class, [
                'field_type' => DateRangePickerType::class,
                'field_options' => [
                    'field_options' => [
                        'format' => 'dd.MM.yyyy'
                    ]
                ]
            ])
            ->add('invoiceDateDue', DateRangeFilter::class, [
                'field_type' => DateRangePickerType::class,
                'field_options' => [
                    'field_options' => [
                        'format' => 'dd.MM.yyyy'
                    ]
                ]
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

        // if (!$unitId) {
        //     $filter
        //         ->add('account.unit', null, [
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
            ->add('account', null, [
                'label' => 'Paying Bank Account',
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Account::class,
                    'choice_label' => 'name',
                    // 'choice_label' => !$unitId ? 'nameWithCurrencyUnitBalance' : 'nameWithCurrencyBalance',
                    // 'query_builder' => function (EntityRepository $er) use ($unitId, $accountIds) {
                    //     if (!$unitId) {
                    //         return $er->createQueryBuilder('u')
                    //             ->join('a.unit', 'u')
                    //             ->andWhere('u.active = :active')
                    //             ->orderBy('a.name', 'ASC')
                    //             ->setParameter('active', true)
                    //         ;
                    //     } else {
                    //         return $er->createQueryBuilder('a')
                    //             ->andWhere('a.id IN (:accountIds)')
                    //             ->orderBy('a.name', 'ASC')
                    //             ->setParameter('accountIds', $accountIds)
                    //         ;
                    //     }
                    // },
                ],
            ])
        ;

        $filter
            ->add('supplier', null, [
                'label' => 'Supplier',
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Supplier::class,
                    'choice_label' => 'name',
                    // 'choice_label' => !$unitId ? 'nameWithUnit' : 'name',
                    // 'query_builder' => function (EntityRepository $er) use ($unitId) {
                    //     if (!$unitId) {
                    //         return $er->createQueryBuilder('su')
                    //             ->join('su.unit', 'u')
                    //             ->andWhere('u.active = :active')
                    //             ->orderBy('su.name', 'ASC')
                    //             ->setParameter('active', true)
                    //         ;
                    //     } else {
                    //         return $er->createQueryBuilder('s')
                    //             ->where('s.unit = :unitId')
                    //             ->orderBy('s.name', 'ASC')
                    //             ->setParameter('unitId', $unitId)
                    //         ;
                    //     }
                    // },
                ],
            ])
            ->add('department', null, [
                'label' => 'Department',
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Department::class,
                    'choice_label' => 'name',
                    // 'choice_label' => !$unitId ? 'nameWithUnit' : 'name',
                    // 'query_builder' => function (EntityRepository $er) use ($unitId) {
                    //     if (!$unitId) {
                    //         return $er->createQueryBuilder('d')
                    //             ->join('d.unit', 'u')
                    //             ->andWhere('u.active = :active')
                    //             ->orderBy('d.name', 'ASC')
                    //             ->setParameter('active', true)
                    //         ;
                    //     } else {
                    //         return $er->createQueryBuilder('s')
                    //             ->where('s.unit = :unitId')
                    //             ->orderBy('s.name', 'ASC')
                    //             ->setParameter('unitId', $unitId)
                    //         ;
                    //     }
                    // },
                ],
            ])
            ->add('description', null, [
                'label' => 'Description'
            ])
            ->add('approvalStatus', ChoiceFilter::class, [
                'field_type' => ChoiceType::class,
                'placeholder' => 'Choose an option',
                'field_options' => [
                    'choices' => [
                        'In Review' => 'In Review',
                        'Approved' => 'Approved',
                        'Denied' => 'Denied'
                    ]
                ]
            ])
            ->add('paymentStatus', ChoiceFilter::class, [
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Unpaid' => 'Unpaid',
                        'Part-Paid' => 'Part-Paid',
                        'Paid' => 'Paid',
                        'Money Returned' => 'Money Returned',
                    ],
                    'multiple' => true
                ]
            ])
            ->add('priority', ChoiceFilter::class, [
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'LOW' => 'LOW',
                        'MEDIUM' => 'MEDIUM',
                        'HIGH' => 'HIGH'
                    ],
                ]
            ])
        ;
    }

    // MARK: - List Fields
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('invoiceNumber', null, [
                'label' => 'Doc No.',
                'header_style' => 'width: 40%;'
            ])
            ->add('invoiceDate', null, [
                'label' => 'Date',
                'pattern' => 'd/M/Y',
            ])
            ->add('invoiceDateDue', null, [
                'label' => 'Date Due',
                'pattern' => 'd/M/Y',
            ])
            ->add('description', null, [
                'label' => 'Description',
                'header_style' => 'width: 30%;'
            ])
            ->add('supplier.name', null, [
                'label' => 'Supplier'
            ])
            ->add('approvalStatus', null, [
                'label' => 'Approval Status',
                'template' => 'Invoice/list_approval_status.html.twig',
                'header_style' => 'width: 90px;'
            ])
            ->add('paymentStatus', null, [
                'label' => 'Payment Status',
                'template' => 'Invoice/list_payment_status.html.twig',
                'header_style' => 'width: 90px;'
            ])
            ->add('invoiceDatePaid', null, [
                'label' => 'Date Paid',
                'pattern' => 'd/M/Y',
            ])
            ->add('priority', null, [
                'row_align' => 'center',
                'header_style' => 'text-align: center',
            ])
                /*
            ->add('account', null, [
                'label' => 'Paying Bank Account',
                'header_style' => 'width: 30%;'
            ])
            */
            /*
            ->add('currency')
            */
            ->add('amount', MoneyType::class, [
                'template' => 'CRUD/list_amount.html.twig',
                'row_align' => 'right',
                'header_style' => 'text-align: right',
            ])
            /* TODO: zip download part-paid files also */
            // ->add('file', null, [
            //     'label' => 'Doc File(s)',
            //     'template' => 'CRUD/list_document_file.html.twig',
            //     'header_style' => 'width: 6%;'
            // ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'addBankFee' => [
                        'template' => 'Invoice/list__action_add_bank_fee.html.twig',
                    ],
                    'show' => [],
                    'edit' => [
                        'template' => 'CRUD/list__action_edit_no_label.html.twig',
                    ],
                    'delete' => [],
                ],
            ])
            ->add('comments', null, [
                'template' => 'CRUD/list_comments.html.twig',
            ])
        ;
    }

    // MARK: - Form Fields
    protected function configureFormFields(FormMapper $form): void
    {
        $now = new DateTime();

        // /* Get logged user data */
        // $currentUserData = $this->getCurrentUserData();
        // /* Get unit */
        // $unitId = $this->getUnitId();

        $editRoute = $this->isCurrentRoute('edit');

        $invoiceCurrency = '';
        $invoicePaymentStatus = '';
        $realAmountPaid = '';
        $moneyReturnedTransactions = [];
        $partPaymentTransactions = [];
        $accountCurrency = null;

        if ($editRoute) {
            /** @var Invoice $subject */
            $subject = $this->getSubject();
            $invoiceCurrency = $subject->getCurrency();

            $paymentStatus = $subject->getPaymentStatus();
            $accountCurrency =
                $invoicePaymentStatus == 'Paid' ? $subject->getAccount()->getCurrency() : $invoiceCurrency;
            $realAmountPaid = $subject->getRealAmountPaid();

            foreach ($subject->getTransactions() as $transaction) {
                if ($transaction->getTransactionType()::MONEY_RETURN) {
                    $moneyReturnedTransactions[] = $transaction;
                }

                if ($transaction->getInvoicePartPayment()) {
                    $partPaymentTransactions[] = $transaction;
                }
            }

            $invoicePartPayments = $subject->getInvoicePartPayments()->toArray();
        }

        // $bankAccountTypeId = $currentUserData['bankAccountTypeId'] ?? null;

        // if ($unitId) {
        if ($editRoute) {
            $form
                ->with('Invoice number', [
                    'label' => 'Invoice - ' . $subject->getInvoiceNumber(),
                    'class' => 'col-md-6'
                ])
            ;
        } else {
            $form
                ->with('New invoice', ['class' => 'col-md-6'])
            ;
        }
                $form
                    ->add('invoiceNumber', null, [
                        'label' => 'Invoice No',
                    ])
                    ->add('invoiceDate', DatePickerType::class, [
                        'years' => range(1900, $now->format('Y')),
                        'dp_max_date' => $now->format('c'),
                        'required' => true,
                        'format' => 'dd.MM.yyyy',
                    ])
                ;

        if ($editRoute) {
            $form
                ->add('currency', TextType::class, [
                    // 'disabled' => true,
                    'attr' => [
                        'readonly' => 'readonly',
                    ],
                ])
            ;
        } else {
            $form
                ->add('currency', CurrencyType::class, [
                    'placeholder' => 'Choose an option',
                    'choice_label' => function ($currencyCode) {
                        $currencyName = Currencies::getName($currencyCode);

                        return sprintf('%s - %s', $currencyCode, $currencyName);
                    },
                    'preferred_choices' => $this->preferredCurrencyChoices
                ])
            ;
        }

                $form
                    ->add('budget', EntityType::class, [
                        'class' => Budget::class,
                        'required' => false,
                        'choice_label' => function (Budget $budget) {
                        // 'choice_label' => function(Budget $budget) use ($unitId) {
                            // if (!$unitId) {
                            //     if ($budget->getUnit()) {
                            //         return $budget->getName() . ' (' . $budget->getUnit()->getName() . ')';
                            //     } else {
                            //         return '';
                            //     }
                            // } else {
                                return $budget->getName();
                            // }
                        },
                        'query_builder' => function (EntityRepository $er) {
                        // 'query_builder' => function (EntityRepository $er) use ($unitId) {
                            // if ($unitId) {
                            //     return $er->createQueryBuilder('b')
                            //         ->where('b.unit = :unit')
                            //         ->addOrderBy('b.startDate', 'ASC')
                            //         ->setParameter('unit', $unitId)
                            //     ;
                            // } else {
                                return $er->createQueryBuilder('b')
                                    ->addOrderBy('b.startDate', 'ASC')
                                ;
                            // }
                        },
                    ])
                    ->add('department', ModelListType::class, [
                        'class' => Department::class,
                        'btn_delete' => false,
                        'btn_list' => 'Select',
                        'required' => false,
                    ])
                    ->add('description', TextareaType::class, [
                        'required' => false,
                        'label' => 'Description',
                    ])
                    ->add('supplier', ModelListType::class, [
                        'class' => Supplier::class,
                        'btn_delete' => false,
                        'btn_list' => 'Select',
                        'required' => true
                    ])
                    ->add('invoiceDateDue', DatePickerType::class, [
                        'years' => range(1900, $now->format('Y')),
                        'dp_max_date' => $now->format('c'),
                        'required' => true,
                        'format' => 'dd.MM.yyyy',
                    ])
                    ->add('priority', ChoiceType::class, [
                        'label' => 'Priority',
                        'required' => true,
                        'placeholder' => false,
                        'choices' => [
                            'LOW' => 'LOW',
                            'MEDIUM' => 'MEDIUM',
                            'HIGH' => 'HIGH',
                        ],
                    ])
                    ->add('approvalStatus', ChoiceType::class, [
                        'label' => 'Approval Status',
                        'required' => true,
                        'placeholder' => false,
                        'choices' => [
                            'In Review' => 'In Review',
                            'Approved' => 'Approved',
                            'Denied' => 'Denied'
                        ],
                    ])
            ->end()
            ->with('Payments', [
                'class' => 'col-md-6 mt-5'
                ])
                ->add('paymentStatus', ChoiceType::class, [
                    'label' => 'Payment Status',
                    'required' => true,
                    'placeholder' => false,
                    'choices' => [
                        'Unpaid' => 'Unpaid',
                        'Part-Paid' => 'Part-Paid',
                        'Paid' => 'Paid',
                    ],
                ])
                ->add('account', EntityType::class, [
                    'label' => 'Paying Bank Account',
                    'class' => Account::class,
                    'choice_label' => 'nameWithCurrencyBalance',
                    'placeholder' => 'Select an Account',
                    'required' => false,
                    // 'query_builder' => function (EntityRepository $er) use ($bankAccountTypeId, $unitId) {
                    //     if (!$unitId) {
                    //         return $er->createQueryBuilder('a')
                    //             ->andWhere('a.accountType = :bankAccountTypeId')
                    //             ->andWhere('a.deactivated = false')
                    //             ->setParameters([
                    //                 'bankAccountTypeId' => $bankAccountTypeId,
                    //             ])
                    //             ->orderBy('a.currency', 'ASC')
                    //             ->addOrderBy('a.name', 'ASC')
                    //         ;
                    //     } else {
                    //         return $er->createQueryBuilder('a')
                    //             ->andWhere('a.accountType = :bankAccountTypeId')
                    //             ->andWhere('a.deactivated = false')
                    //             ->andWhere('a.unit = :unit')
                    //             ->setParameters([
                    //                 'bankAccountTypeId' => $bankAccountTypeId,
                    //                 'unit' => $unitId
                    //             ])
                    //             ->orderBy('a.currency', 'ASC')
                    //             ->addOrderBy('a.name', 'ASC')
                    //         ;
                    //     }
                    // },
                ])
                ->add('invoiceDatePaid', DatePickerType::class, [
                    'years' => range(1900, $now->format('Y')),
                    'dp_max_date' => $now->format('c'),
                    'required' => false,
//                        'disabled' => $editRoute && $invoicePaymentStatus !== 'Unpaid',
                    'format' => 'dd.MM.yyyy',
                ])
                ->add('amount', MoneyType::class, [
                    'label' => 'Amount (Invoice Lines Total)',
                    'grouping' => true, // NOTE: Currency digit grouping; it is needed for validation to pass
                    'currency' => $editRoute ? $invoiceCurrency : null,
                    'disabled' => true,
                    'required' => false,
                ])
                ;

        if ($editRoute) {
            $form
                ->add('totalPaid', MoneyType::class, [
                    'label' => 'Total Paid',
                    'grouping' => true, // NOTE: Currency digit grouping; it is needed for validation to pass
                    'currency' => $invoiceCurrency,
                    'required' => false,
                    'disabled' => true,
                ])
            ;
        } else {
            $form
                ->add('totalPaid', TextType::class, [
                    'label' => 'Total Paid',
                    'required' => false,
                    'disabled' => true,
                ])
            ;
        }

                $form
                    ->add('realAmountPaid', MoneyType::class, [
                        'label' => 'Real Amount Paid',
                        'grouping' => true, // NOTE: It is needed for validation to pass
                        'currency' => $editRoute ? $accountCurrency : null,
                        'required' => false,
                        'disabled' => $editRoute && !empty($invoicePartPayments) && $realAmountPaid > 0,
                    ])
                    ->add('bankFeeAmount', MoneyType::class, [
                        'grouping' => true, // NOTE: It is needed for validation to pass
                        'currency' => $editRoute ? $accountCurrency : null,
                        'required' => false,
                    ])
                    ->add('invoicePartPayments', CollectionType::class, [
                        'label' => 'Part Payments',
                        'required' => false,
                        'type_options' => [
                            'delete' => false,
                        ],
                        'constraints' => [
                            new InvoicePartPaymentsTotal(),
                            new PartPaymentValue(),
                        ],
                    ], [
                        'edit' => 'inline',
                        'inline' => 'blocks',
                        'sortable' => 'position',
                    ])
                ->end()
            ;

        if ($editRoute) {
            $form
                ->with('Money Returns', [
                    'class' => (($paymentStatus == 'Unpaid' && $moneyReturnedTransactions)
                        || ($paymentStatus == 'Paid')
                        || !empty($partPaymentTransactions)
                        ? 'col-md-6' : 'hidden') . ' col-sm-12'
                ])
                    ->add('moneyReturnedButton', CustomButtonType::class, [
                        'label' => false,
                        'mapped' => false,
                    ])
                    ->add('moneyReturns', EmptyType::class, [
                        'label' => false,
                        'mapped' => false,
                        'required' => false,
                        'attr' => [
                            'disabled' => true,
                            'readonly' => true,
                        ],
                    ])
                ->end()
            ;
        }

            $form
                ->with('File Uploads', [
                    'class' => 'col-sm-12'
                ])
                    // ->add('file', CollectionType::class, [
                    //     'label' => 'Upload File(s)',
                    //     'required' => false,
                    //     'help' => 'Supported file types: JPG/JPEG, PNG and PDF',
                    //     'label_attr' => ['data-class' => 'lb-big'],
                    //     'by_reference' => false,
                    // ], [
                    //     'edit' => 'inline',
                    //     'inline' => 'form',
                    //     'sortable'  => 'position',
                    // ])
                ->end()
                ->with('Invoice Lines', ['class' => 'col-sm-12'])
                    ->add('invoiceLines', CollectionType::class, [
                        'label' => false,
                        'constraints' => [
                            new NoInvoiceLineAdded(),
                            new LineNetTotalDifference(),
                        ],
                    ], [
                        'edit' => 'inline',
                        'inline' => 'form',
                        'sortable' => 'position',
                    ])
                ->end()
                ->with('Comments', ['class' => 'col-sm-12'])
                    ->add('comments', CollectionType::class, [
                        'label' => false,
                        'required' => false,
                    ], [
                        'edit' => 'inline',
                        'inline' => 'form',
                        'sortable'  => 'position',
                    ])
                ->end()
            ;
        // } else {
        //     /* TODO: make better solution, like custom type which will have custom template */
        //     $form
        //         ->add('emptyType', EmptyType::class, [
        //             'label' => 'Please select a Yacht to create and edit Invoices',
        //             'mapped' => false,
        //             'required' => false
        //         ])
        //     ;
        // }
    }

    // MARK: - Show Fields
    protected function configureShowFields(ShowMapper $show): void
    {
        /** @var Invoice $subject */
        $subject = $this->getSubject();

        // /* Get unit */
        // $unitId = $this->getUnitId();

        $title = $subject->__toString();
        $accountCurrency = $subject->getAccount() ? $subject->getAccount()->getCurrency() : null;
        $invoiceCurrency = $subject->getCurrency();
        $invoiceTransactions = $subject->getTransactions()->toArray();

        $partPaymentTransactionsPaid = [];
        $moneyReturnedTransactions = [];

        foreach ($invoiceTransactions as $transaction) {
            if ($transaction->getTransactionTypeName() == 'Money Returned') {
                $moneyReturnedTransactions[] = $transaction;
            }
        }

        foreach ($subject->getInvoicePartPayments() as $partPayment) {
            if ($partPayment->getMoneyReturnedAmount() == null && $partPayment->getMoneyReturnedDate() == null) {
                $partPaymentTransactionsPaid[] = $partPayment;
            }
        }

        $show
            ->with('', [
                'label' => $title,
                'class' => 'col-md-6'
            ])
        ;

        // if (!$unitId) {
        //     $show
        //         ->add('unit.name', null, [
        //             'label' => 'Unit',
        //         ])
        //     ;
        // }

        $show
            ->add('invoiceNumber')
            ->add('invoiceDate', 'date', [
                'label' => 'Date',
                'format' => 'd/m/Y'
            ])
            ->add('account', null, [
                'label' => 'Paying Bank Account',
            ])
            ->add('currency')
            ->add('budget.name', null, [
                'label' => 'Budget'
            ])
            ->add('department.name', null, [
                'label' => 'Department'
            ])
            ->add('description', null, [
                'label' => 'Description'
            ])
            ->add('supplier.name', null, [
                'label' => 'Supplier'
            ])
            ->add('invoiceDateDue', 'date', [
                'label' => 'Date Due',
                'format' => 'd/m/Y'
            ])
            ->add('priority')
            ->add('approvalStatus', null, [
                'template' => 'Invoice/approval_status_show_field.html.twig'
            ])
            ->add('paymentStatus', null, [
                'template' => 'Invoice/payment_status_show_field.html.twig'
            ])
            ->add('invoiceDatePaid', 'date', [
                'label' => 'Date Paid',
                'format' => 'd/m/Y'
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Total Amount',
                'template' => 'CRUD/show_currency.html.twig',
                'currency' => ($accountCurrency !== $invoiceCurrency) ? $invoiceCurrency : $accountCurrency
            ])
            ->add('realAmountPaid', MoneyType::class, [
                'label' => 'Real Amount Paid',
                'template' => 'CRUD/show_currency.html.twig',
                'currency' => $accountCurrency
            ])
            ->add('bankFeeAmount', MoneyType::class, [
                'label' => 'Bank Fee',
                'template' => 'CRUD/show_currency.html.twig',
                'currency' => $accountCurrency
            ])
            ->add('restPaymentTotal', MoneyType::class, [
                'label' => 'Rest to Pay',
                'template' => 'CRUD/show_currency.html.twig',
                'currency' => ($accountCurrency !== $invoiceCurrency) ? $invoiceCurrency : $accountCurrency
            ])
            ->add('totalPaid', MoneyType::class, [
                'label' => 'Total Paid',
                'template' => 'CRUD/show_currency.html.twig',
                'currency' => ($accountCurrency !== $invoiceCurrency) ? $invoiceCurrency : $accountCurrency
            ])
            ->add('addedByUserDateTime', null, [
                'label' => 'Added By'
            ])
            ->add('editedByUserDateTime', null, [
                'label' => 'Last Edit By'
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
        ->with('Invoice Lines and Comments', ['label' => 'Invoice Lines', 'class' => 'col-sm-12'])
            ->add('invoiceLines', CollectionType::class, [
                'template' => 'Invoice/invoice_line_show_field.html.twig',
                'currency' => ($accountCurrency !== $invoiceCurrency) ? $invoiceCurrency : $accountCurrency
            ])
        ->end()
        ;

        if (!empty($partPaymentTransactionsPaid)) {
            $show
                ->with('Part Payments')
                ->add('invoicePartPayments', CollectionType::class, [
                    'template' => 'Invoice/part_payments_show_field.html.twig',
                    ])
                ->end()
            ;
        }

        if (!empty($moneyReturnedTransactions)) {
            $show
                ->with('Money Returns')
                    ->add('moneyReturns', CollectionType::class, [
                        'label' => false,
                        'template' => 'Invoice/money_returns_show_field.html.twig',
                ])
                ->end()
            ;
        }

        if ($subject->getComments()->count() > 0) {
            $show
                ->with('Comments', ['label' => 'Comments', 'class' => 'col-sm-12'])
                    ->add('comments', CollectionType::class, [
                        'template' => 'Invoice/comments_show_field.html.twig',
                    ])
                ->end()
            ;
        }
    }

    // MARK: - PrePersist
    /**
     * @throws Exception
     */
    protected function prePersist(object $object): void
    {
        /** @var Invoice $object */

        /** @var InvoiceLineRepository $invoiceLineRepository */
        $invoiceLineRepository = $this->entityManager->getRepository(InvoiceLine::class);
        /** @var UnitRepository $unitRepository */
        $unitRepository = $this->entityManager->getRepository(Unit::class);
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        // /* Get unit */
        // $unitId = $this->getUnitId();

        $data = $this->getDataForCreateAndUpdate($object);
        $currentUser = $this->getCurrentUserData();

        $invoiceLines = $object->getInvoiceLines()->toArray();
        $invoicePartPayments = $object->getInvoicePartPayments()->toArray();

        $account = $data['account'];
        $dateTime = $data['dateTime'];
        $currency = $data['currency'];
        $invoiceCurrency = $object->getCurrency();
        $approvalStatus = $data['approvalStatus'];
        $paymentStatus = $data['paymentStatus'];
        $amountTotal = $data['amountTotal'];
        $realAmountPaid = $data['realAmountPaid'];
        $user = $currentUser['user'];
        $userAndDateTime = $this->getUserAndDateTime();
        $bankPaymentTransactionType = TransactionType::BANK_PAYMENT;
        $bankFeeTransactionType = TransactionType::ACCOUNT_CHARGE;

        // if ($unitId) {
        //     $unit = $unitRepository->findOneBy([
        //         'id' => $unitId
        //     ]);
        // } else {
            $unit = $account->getUnit();
        // }

        if (!$object->getUnit()) {
            $object->setUnit($unit);
        }

        if (!$object->getCurrency()) {
            $object->setCurrency($currency);
        }

        if (!$approvalStatus) {
            $object->setApprovalStatus('In review');
        }

        if (!$paymentStatus) {
            $object->setPaymentStatus('Unpaid');
        } elseif ($paymentStatus == 'Paid') {
            if (!$object->getInvoiceDatePaid()) {
                $datePaid = $object->getInvoiceDate();
            } else {
                $datePaid = $object->getInvoiceDatePaid();
            }

            if (($currency !== $invoiceCurrency) && $realAmountPaid) {
                $object->setRealCurrencyPaid($currency);
                $account->setBalance($realAmountPaid, false);
            } else {
                $account->setBalance($amountTotal, false);
            }

            // /* Adding comment(s) management */
            // $this->manageEmbeddedCommentAdmin($object);

            $lastTransactionBalance = $this->getLastTransactionBalance($datePaid, $account);

            if (($currency !== $invoiceCurrency) && $realAmountPaid) {
                $balance = $lastTransactionBalance - $realAmountPaid;
            } else {
                $balance = $lastTransactionBalance - $amountTotal;
            }

            /* New record in transaction table */
            $invoiceTransaction = $this->transactionService->createOrUpdateInvoiceTransaction(
                $object,
                $datePaid,
                $unit,
                $bankPaymentTransactionType,
                $account,
                $currency,
                $invoiceCurrency,
                $amountTotal,
                $userAndDateTime,
                $balance,
                'create',
                $realAmountPaid
            );

            if (($currency !== $invoiceCurrency) && $realAmountPaid) {
                $this->updateLaterTransactionsBalancesTransaction(
                    $account,
                    $invoiceTransaction,
                    $datePaid,
                    $realAmountPaid,
                    false
                );
            } else {
                $this->updateLaterTransactionsBalancesTransaction(
                    $account,
                    $invoiceTransaction,
                    $datePaid,
                    $amountTotal,
                    false
                );
            }

            if ($object->isBankFeeAdded()) {
                $dateTime = new DateTimeImmutable();

                $bankFeeAmount = $object->getBankFeeAmount();
                $account->setBalance($bankFeeAmount, false);

                $lastTransactionBalance = $this->getLastTransactionBalance($datePaid, $account);
                $balance = floatval($lastTransactionBalance) - floatval($bankFeeAmount);

                /* New record in transaction table */
                $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                    $datePaid,
                    $unit,
                    $bankFeeTransactionType,
                    $account,
                    $invoiceTransaction,
                    $bankFeeAmount,
                    $userAndDateTime,
                    $balance,
                    'create'
                );

                $this->updateLaterTransactionsBalancesTransaction(
                    $account,
                    $bankFeeTransaction,
                    $datePaid,
                    $bankFeeAmount,
                    false
                );

                $invoiceTransaction->setBankFeeAdded(true);
                $invoiceTransaction->setBankFeeAmount($bankFeeAmount);
                $invoiceTransaction->setBankFeeCurrency($currency);
            }
        } elseif ($paymentStatus == 'Part-Paid') {
            $this->addOrDeletePartPaymentRows($object, $account, $currency, $unit, $invoicePartPayments);

            $bankFeeInvoiceTotal = 0;

            foreach ($invoicePartPayments as $partPayment) {
                if ($partPayment->isBankFeeAdded()) {
                    $bankFeeAmount = $partPayment->getBankFeeAmount();
                    $partPaymentTransaction = $partPayment->getTransaction();

                    $lastTransactionBalance = $this->getLastTransactionBalance(
                        $partPaymentTransaction->getDate(),
                        $account
                    );

                    $balance = floatval($lastTransactionBalance) - floatval($bankFeeAmount);

                    /* New record in transaction table */
                    $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                        $partPayment->getDatePaid(),
                        $unit,
                        $bankFeeTransactionType,
                        $account,
                        $partPaymentTransaction,
                        $bankFeeAmount,
                        $userAndDateTime,
                        $balance,
                        'create'
                    );

                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $bankFeeTransaction,
                        $bankFeeTransaction->getDate(),
                        $bankFeeAmount,
                        false
                    );

                    $partPaymentTransaction->setBankFeeAdded(true);
                    $partPaymentTransaction->setBankFeeAmount($bankFeeAmount);

                    $transactionRepository->add($partPaymentTransaction, true);

                    $account->setBalance($bankFeeAmount, false);

                    $bankFeeInvoiceTotal += $partPayment->getBankFeeAmount();
                }
            }

            $object->setBankFeeAmount(strval($bankFeeInvoiceTotal));
            $object->setRestPaymentTotal($data['restPaymentTotal']);

            $invoiceAmount = 0;

            foreach ($object->getInvoiceLines() as $line) {
                $invoiceAmount += $line->getLineTotal();
            }

            if ($data['totalPartPaymentAmount'] == $invoiceAmount) {
                $allBankFeesAddedInPartPayments = true;

                foreach ($invoicePartPayments as $partPayment) {
                    if (!$partPayment->isBankFeeAdded()) {
                        $allBankFeesAddedInPartPayments = false;
                    }
                }

                if ($allBankFeesAddedInPartPayments) {
                    $object->setBankFeeAdded(true);
                }
            }
        }

        /* Persist created data */
        $object->setDateTimeAdded($dateTime);
        $object->setAddedByUser($user);

        if ($paymentStatus == 'Paid') {
            $object->setRestPaymentTotal('0');
            $object->setTotalPaid($amountTotal);
        } elseif ($paymentStatus == 'Unpaid') {
            $object->setRestPaymentTotal($amountTotal);
        }

        $object->setAmount($amountTotal);

        // /* Adding comment(s) management */
        // $this->manageEmbeddedCommentAdmin($object);
    }

    // MARK: - PreUpdate
    /**
     * @throws Exception
     */
    protected function preUpdate(object $object): void
    {
        /** @var Invoice $object */

        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->entityManager->getRepository(Account::class);
        /** @var InvoiceRepository $invoiceRepository */
        $invoiceRepository = $this->entityManager->getRepository(Invoice::class);
        /** @var InvoiceLineRepository $invoiceLineRepository */
        $invoiceLineRepository = $this->entityManager->getRepository(InvoiceLine::class);
        /** @var InvoicePartPaymentRepository $invoicePartPaymentRepository */
        $invoicePartPaymentRepository = $this->entityManager->getRepository(InvoicePartPayment::class);
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        $data = $this->getDataForCreateAndUpdate($object);
        $currentUser = $this->getCurrentUserData();

        $user = $currentUser['user'];
        $unit = $object->getUnit();
        $account = $data['account'];
        $dateTime = $data['dateTime'];
        $currency = $data['currency'];
        $invoiceCurrency = $object->getCurrency();
        $paymentStatus = $data['paymentStatus'];
        $restPaymentTotalNew = $data['restPaymentTotal'];
        $amountTotal = $data['amountTotal'];
        $amountTotalDifference = $data['amountTotalDifference'];
        $realAmountPaid = $data['realAmountPaid'];
        $realAmountPaidDifference = $data['realAmountPaidDifference'];
        $originalAccount = $data['originalAccount'];
        $originalCurrency = $data['originalCurrency'];
        $originalTotalAmount = $data['originalTotalAmount'];
        $originalRealAmountPaid = $data['originalRealAmountPaid'];
        $originalPaymentStatus = $data['originalPaymentStatus'];
        $originalBankFeeAdded = $data['originalBankFeeAdded'];
        $originalBankFeeAmount = $data['originalBankFeeAmount'];
        $bankFeeAmountDifference = $data['bankFeeAmountDifference'];
        $originalInvoiceDatePaid = $data['originalInvoiceDatePaid'];
        $userAndDateTime = $this->getUserAndDateTime();
        $bankPaymentTransactionType = TransactionType::BANK_PAYMENT;
        $bankFeeTransactionType = TransactionType::ACCOUNT_CHARGE;

        if ($object->getInvoiceDate() !== $object->getInvoiceDatePaid()) {
            if (!$object->getInvoiceDatePaid()) {
                $datePaid = $object->getInvoiceDate();
            } else {
                $datePaid = $object->getInvoiceDatePaid();
            }
        } else {
            $datePaid = $object->getInvoiceDate();
        }

        // /* Adding comment(s) management */
        // $this->manageEmbeddedCommentAdmin($object);

        /* Persist updated data */
        if (
            $paymentStatus == 'Paid' && $originalPaymentStatus == 'Unpaid'
            || $paymentStatus == 'Paid' && $originalPaymentStatus == 'Part-Paid'
        ) {
            if ($originalPaymentStatus == 'Part-Paid') {
                /* Reset data from Part-Paid status to blank */
                $invoicePartPayments = $object->getInvoicePartPayments()->toArray();

                // $invoice->setInvoiceDatePaid(null);
                $object->setRestPaymentTotal($originalTotalAmount);
                $object->setTotalPaid('0');

                foreach ($invoicePartPayments as $partPayment) {
                    $amount = $partPayment->getAmount();
                    $account->setBalance($amount, true);

                    $partPaymentTransaction = $partPayment->getTransaction();

                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $partPaymentTransaction,
                        $partPaymentTransaction->getDate(),
                        $partPaymentTransaction->getAmount(),
                        true
                    );

                    if ($partPaymentTransaction->isBankFeeAdded()) {
                        $bankFeeAmount = $partPaymentTransaction->getBankFeeAmount();
                        $bankFeeTransaction = $partPaymentTransaction->getTransactions()->toArray()[0];

                        $account->setBalance($bankFeeAmount, true);

                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $bankFeeTransaction,
                            $bankFeeTransaction->getDate(),
                            $bankFeeAmount,
                            true
                        );
                    }

                    $transactionRepository->remove($partPaymentTransaction, true);
                }

                $object->setBankFeeAmount($object->getBankFeeAmount());

                $accountRepository->add($account);
            }

            $accountCurrency = $currency;
            $invoiceCurrency = $object->getCurrency();

            if ($object->getInvoiceDate() !== $object->getInvoiceDatePaid()) {
                if (!$object->getInvoiceDatePaid()) {
                    $datePaid = $object->getInvoiceDate();
                } else {
                    $datePaid = $object->getInvoiceDatePaid();
                }
            } else {
                $datePaid = $object->getInvoiceDate();
            }

            $lastTransactionBalance = $this->getLastTransactionBalance($datePaid, $account);

            if (($accountCurrency !== $invoiceCurrency) && $realAmountPaid) {
                $object->setRealCurrencyPaid($accountCurrency);
                $account->setBalance($realAmountPaid, false);
                $balance = $lastTransactionBalance - $realAmountPaid;
            } else {
                $account->setBalance($amountTotal, false);
                $balance = $lastTransactionBalance - $amountTotal;
            }

            /* New record in transaction table */
            $invoiceTransaction = $this->transactionService->createOrUpdateInvoiceTransaction(
                $object,
                $datePaid,
                $unit,
                $bankPaymentTransactionType,
                $account,
                $currency,
                $invoiceCurrency,
                $amountTotal,
                $userAndDateTime,
                $balance,
                'create',
                $realAmountPaid
            );

            if (($accountCurrency !== $invoiceCurrency) && $realAmountPaid) {
                $this->updateLaterTransactionsBalancesTransaction(
                    $account,
                    $invoiceTransaction,
                    $invoiceTransaction->getDate(),
                    $realAmountPaid,
                    false
                );
            } else {
                $this->updateLaterTransactionsBalancesTransaction(
                    $account,
                    $invoiceTransaction,
                    $invoiceTransaction->getDate(),
                    $amountTotal,
                    false
                );
            }

            $object->setTotalPaid($amountTotal);
            $object->setRestPaymentTotal('0');

            if ($object->isBankFeeAdded()) {
                $bankFeeTransaction = null;

                $bankFeeAmount = $object->getBankFeeAmount();
                $account->setBalance($bankFeeAmount, false);

                $dateTime = new DateTimeImmutable();

                $lastTransactionBalance = $this->getLastTransactionBalance($datePaid, $account);
                $balance = floatval($lastTransactionBalance) - floatval($bankFeeAmount);

                /* New record in transaction table */
                $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                    $datePaid,
                    $unit,
                    $bankFeeTransactionType,
                    $account,
                    $invoiceTransaction,
                    $bankFeeAmount,
                    $userAndDateTime,
                    $balance,
                    'create'
                );

                $this->updateLaterTransactionsBalancesTransaction(
                    $account,
                    $bankFeeTransaction,
                    $bankFeeTransaction->getDate(),
                    $bankFeeAmount,
                    false
                );

                $invoiceTransaction->setBankFeeAdded(true);
                $invoiceTransaction->setBankFeeAmount($bankFeeAmount);
                $invoiceTransaction->setBankFeeCurrency($accountCurrency);
            }
        } elseif ($paymentStatus == 'Paid' && $originalPaymentStatus == 'Paid') {
            /* If there is change in invoice dates and/or in account */
            if (
                ($originalInvoiceDatePaid->format('d/m/Y') !== $datePaid->format('d/m/Y'))
                || ($currency !== $originalCurrency) || ($account !== $originalAccount)
            ) {
                $accountCurrency = $currency;
                $invoiceCurrency = $object->getCurrency();
                $transactions = $object->getTransactions()->toArray();

                $transaction = null;

                foreach ($transactions as $item) {
                    if ($item->getTransactionTypeName() == 'Bank Payment') {
                        $transaction = $item;
                    }
                }

                if ($originalAccount !== $account) {
                    if (($accountCurrency !== $invoiceCurrency) && $realAmountPaid) {
                        if ($originalAccount->getCurrency() == $invoiceCurrency) {
                            $originalAccount->setBalance($originalTotalAmount, true);
                            $this->updateLaterTransactionsBalancesTransaction(
                                $originalAccount,
                                $transaction,
                                $transaction->getDate(),
                                $originalTotalAmount,
                                true
                            );

                            $account->setBalance($realAmountPaid, false);
                            $object->setRealCurrencyPaid($account->getCurrency());
                        } else {
                            if ($originalRealAmountPaid != $realAmountPaid) {
                                $originalAccount->setBalance($originalRealAmountPaid, true);
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $originalAccount,
                                    $transaction,
                                    $transaction->getDate(),
                                    $originalRealAmountPaid,
                                    true
                                );
                            } else {
                                $originalAccount->setBalance($realAmountPaid, true);
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $originalAccount,
                                    $transaction,
                                    $transaction->getDate(),
                                    $realAmountPaid,
                                    true
                                );
                            }

                            $account->setBalance($realAmountPaid, false);
                        }
                    } else {
                        if ($originalTotalAmount != $amountTotal) {
                            if ($originalAccount->getCurrency() !== $invoiceCurrency) {
                                $originalAccount->setBalance($originalRealAmountPaid, true);
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $originalAccount,
                                    $transaction,
                                    $transaction->getDate(),
                                    $originalRealAmountPaid,
                                    true
                                );

                                $object->setRealAmountPaid(null);
                                $object->setRealCurrencyPaid(null);
                            } else {
                                $originalAccount->setBalance($originalTotalAmount, true);
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $originalAccount,
                                    $transaction,
                                    $transaction->getDate(),
                                    $originalTotalAmount,
                                    true
                                );
                            }
                        } else {
                            if ($originalAccount->getCurrency() !== $invoiceCurrency) {
                                $originalAccount->setBalance($originalRealAmountPaid, true);
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $originalAccount,
                                    $transaction,
                                    $transaction->getDate(),
                                    $originalRealAmountPaid,
                                    true
                                );

                                $object->setRealAmountPaid(null);
                                $object->setRealCurrencyPaid(null);
                            } else {
                                $originalAccount->setBalance($amountTotal, true);
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $originalAccount,
                                    $transaction,
                                    $transaction->getDate(),
                                    $amountTotal,
                                    true
                                );
                            }
                        }

                        if (($accountCurrency !== $invoiceCurrency) && $realAmountPaid) {
                            $account->setBalance($realAmountPaid, false);
                        } else {
                            $account->setBalance($amountTotal, false);
                        }
                    }

                    if (
                        $object->getInvoiceDatePaid()->format('d/m/Y')
                        !== $transaction->getDate()->format('d/m/Y')
                    ) {
                        if (($accountCurrency !== $invoiceCurrency) && $realAmountPaid) {
                            $this->updateLaterTransactionsBalancesTransaction(
                                $account,
                                $transaction,
                                $object->getInvoiceDatePaid(),
                                $realAmountPaid,
                                false
                            );
                        } else {
                            $this->updateLaterTransactionsBalancesTransaction(
                                $account,
                                $transaction,
                                $object->getInvoiceDatePaid(),
                                $amountTotal,
                                false
                            );
                        }

                        $lastTransactionBalance = $this->getLastTransactionBalance(
                            $object->getInvoiceDatePaid(),
                            $account,
                            $transaction->getTransactionNumber()
                        );
                    } else {
                        if (($accountCurrency !== $invoiceCurrency) && $realAmountPaid) {
                            $this->updateLaterTransactionsBalancesTransaction(
                                $account,
                                $transaction,
                                $transaction->getDate(),
                                $realAmountPaid,
                                false
                            );
                        } else {
                            $this->updateLaterTransactionsBalancesTransaction(
                                $account,
                                $transaction,
                                $transaction->getDate(),
                                $amountTotal,
                                false
                            );
                        }

                        $lastTransactionBalance = $this->getLastTransactionBalance(
                            $transaction->getDate(),
                            $account,
                            $transaction->getTransactionNumber()
                        );
                    }
                } else {
                    if (($currency !== $invoiceCurrency) && $realAmountPaid) {
                        if ($realAmountPaid != $originalRealAmountPaid) {
                            $account->setBalance($realAmountPaidDifference, false);

                            $this->updateLaterTransactionsBalancesTransaction(
                                $account,
                                $transaction,
                                $transaction->getDate(),
                                $originalRealAmountPaid,
                                true
                            );
                        } else {
                            $this->updateLaterTransactionsBalancesTransaction(
                                $account,
                                $transaction,
                                $transaction->getDate(),
                                $realAmountPaid,
                                true
                            );
                        }

                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $transaction,
                            $datePaid,
                            $realAmountPaid,
                            false
                        );
                    } else {
                        if ($originalTotalAmount != $amountTotal) {
                            $account->setBalance($amountTotalDifference, false);

                            $this->updateLaterTransactionsBalancesTransaction(
                                $account,
                                $transaction,
                                $transaction->getDate(),
                                $originalTotalAmount,
                                true
                            );
                        } else {
                            $this->updateLaterTransactionsBalancesTransaction(
                                $account,
                                $transaction,
                                $transaction->getDate(),
                                $amountTotal,
                                true
                            );
                        }

                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $transaction,
                            $datePaid,
                            $amountTotal,
                            false
                        );
                    }

                    $lastTransactionBalance = $this->getLastTransactionBalance(
                        $datePaid,
                        $account,
                        $transaction->getTransactionNumber()
                    );
                }
                if (($currency !== $invoiceCurrency) && $realAmountPaid) {
                    $balance = $lastTransactionBalance - $realAmountPaid;
                } else {
                    $balance = $lastTransactionBalance - $amountTotal;
                }

                /* Update record in transaction table */
                $transaction = $this->transactionService->createOrUpdateInvoiceTransaction(
                    $object,
                    $datePaid,
                    $unit,
                    $bankPaymentTransactionType,
                    $account,
                    $currency,
                    $invoiceCurrency,
                    $amountTotal,
                    $userAndDateTime,
                    $balance,
                    'update',
                    $realAmountPaid,
                    $transaction
                );

                if ($object->isBankFeeAdded()) {
                    foreach ($object->getTransactions() as $item) {
                        if ($item->getTransactionTypeName() == 'Account Charge') {
                            $bankFeeTransaction = $item;
                        }
                    }

                    if (isset($bankFeeTransaction)) {
                        $bankFeeAmount = $object->getBankFeeAmount();

                        if ($originalInvoiceDatePaid->format('d/m/Y') !== $datePaid->format('d/m/Y')) {
                            if ($originalAccount !== $account) {
                                $originalAccount->setBalance($originalBankFeeAmount, true);
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $originalAccount,
                                    $bankFeeTransaction,
                                    $bankFeeTransaction->getDate(),
                                    $originalBankFeeAmount,
                                    true
                                );

                                $account->setBalance($bankFeeAmount, false);
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $bankFeeTransaction,
                                    $datePaid,
                                    $bankFeeAmount,
                                    false
                                );
                            } else {
                                if ($originalBankFeeAmount != $bankFeeAmount) {
                                    $account->setBalance($originalBankFeeAmount, true);
                                    $account->setBalance($bankFeeAmount, false);

                                    $this->updateLaterTransactionsBalancesTransaction(
                                        $account,
                                        $bankFeeTransaction,
                                        $originalInvoiceDatePaid,
                                        $originalBankFeeAmount,
                                        true
                                    );
                                    $this->updateLaterTransactionsBalancesTransaction(
                                        $account,
                                        $bankFeeTransaction,
                                        $datePaid,
                                        $bankFeeAmount,
                                        false
                                    );
                                } else {
                                    if ($originalInvoiceDatePaid !== $datePaid) {
                                        if (
                                            $datePaid->format('d/m/Y')
                                            == $bankFeeTransaction->getDate()->format('d/m/Y')
                                        ) {
                                            $this->updateLaterTransactionsBalancesTransaction(
                                                $account,
                                                $bankFeeTransaction,
                                                $bankFeeTransaction->getDate(),
                                                $bankFeeAmount,
                                                true
                                            );
                                        } else {
                                            $this->updateLaterTransactionsBalancesTransaction(
                                                $account,
                                                $bankFeeTransaction,
                                                $originalInvoiceDatePaid,
                                                $bankFeeAmount,
                                                true
                                            );
                                        }

                                        $this->updateLaterTransactionsBalancesTransaction(
                                            $account,
                                            $bankFeeTransaction,
                                            $datePaid,
                                            $bankFeeAmount,
                                            false
                                        );
                                    }
                                }
                            }
                        } else {
                            $originalAccount->setBalance($originalBankFeeAmount, true);
                            $this->updateLaterTransactionsBalancesTransaction(
                                $originalAccount,
                                $bankFeeTransaction,
                                $transaction->getDate(),
                                $originalBankFeeAmount,
                                true
                            );

                            $account->setBalance($bankFeeAmount, false);
                            $this->updateLaterTransactionsBalancesTransaction(
                                $account,
                                $bankFeeTransaction,
                                $datePaid,
                                $bankFeeAmount,
                                false
                            );
                        }

                        $lastTransactionBalance = $this->getLastTransactionBalance(
                            $datePaid,
                            $account,
                            $bankFeeTransaction->getTransactionNumber()
                        );

                        $balance = floatval($lastTransactionBalance) - floatval($bankFeeAmount);

                        /* Update record in transaction table */
                        $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                            $datePaid,
                            $unit,
                            $bankFeeTransactionType,
                            $account,
                            $bankFeeTransaction->getTransaction(),
                            $bankFeeAmount,
                            $userAndDateTime,
                            $balance,
                            'update',
                            $bankFeeTransaction
                        );
                    }
                } else {
                    foreach ($object->getTransactions() as $transaction) {
                        if ($transaction->getTransactionTypeName() == 'Account Charge') {
                            $bankFeeTransaction = $transaction;
                        }
                    }

                    if (isset($bankFeeTransaction)) {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $originalAccount,
                            $bankFeeTransaction,
                            $bankFeeTransaction->getDate(),
                            $originalBankFeeAmount,
                            true
                        );

                        $transactionRepository->remove($bankFeeTransaction, true);

                        $originalAccount->setBalance($originalBankFeeAmount, true);
                    }
                }
            /* Check if there is change in total amount */
            } elseif ($amountTotalDifference != '0') {
                /* Check if there is change in bank fee amount also */
                if ($object->getBankFeeAmount() != $originalBankFeeAmount) {
                    if ($originalBankFeeAmount !== null) {
                        $account->setBalance($originalBankFeeAmount, true);

                        $account->setBalance($amountTotalDifference, false);

                        $transactions = $object->getTransactions()->toArray();

                        foreach ($transactions as $transaction) {
                            if ($transaction->getTransactionTypeName() == 'Bank Payment') {
                                /* If there is a change in date of invoice */
                                if ($originalInvoiceDatePaid !== $object->getInvoiceDatePaid()) {
                                    $this->updateLaterTransactionsBalancesTransaction(
                                        $account,
                                        $transaction,
                                        $transaction->getDate(),
                                        $originalTotalAmount,
                                        true
                                    );

                                    $lastTransactionBalance = $this->getLastTransactionBalance(
                                        $datePaid,
                                        $account,
                                        $transaction->getTransactionNumber()
                                    );

                                    $balance = $lastTransactionBalance - $amountTotal;

                                    $transaction->setDate($datePaid);
                                    $transaction->setDateTimeEdited($dateTime);
                                    $transaction->setAmount($amountTotal);
                                    $transaction->setCurrency($object->getCurrency());
                                    $transaction->setMoneyOut($amountTotal);
                                    $transaction->setBalanceMainAccount(strval($balance));
                                    $transactionRepository->add($transaction, true);

                                    $this->updateLaterTransactionsBalancesTransaction(
                                        $account,
                                        $transaction,
                                        $transaction->getDate(),
                                        $amountTotal,
                                        false
                                    );
                                } else {
                                    $transaction->setDateTimeEdited($dateTime);
                                    $transaction->setAmount($amountTotal);
                                    $transaction->setMoneyOut($amountTotal);
                                    $transaction->setBalanceMainAccount($amountTotalDifference, false);

                                    $this->updateLaterTransactionsBalancesTransaction(
                                        $account,
                                        $transaction,
                                        $datePaid,
                                        $amountTotalDifference,
                                        false
                                    );
                                }
                            }
                        }

                        /* Subtract the new bankFeeAmount and then update the bankFeeTransaction */
                        $account->setBalance($object->getBankFeeAmount(), false);

                        foreach ($object->getTransactions() as $bankFeeTransaction) {
                            if ($bankFeeTransaction->getTransactionTypeName() == 'Account charge') {
                                if (!$object->isBankFeeAdded()) {
                                    $this->updateLaterTransactionsBalancesTransaction(
                                        $account,
                                        $bankFeeTransaction,
                                        $bankFeeTransaction->getDate(),
                                        $object->getBankFeeAmount(),
                                        false
                                    );

                                    $transactionRepository->remove($bankFeeTransaction, true);
                                } else {
                                    $bankFeeTransaction->setDateTimeEdited($dateTime);
                                    $bankFeeTransaction->setAmount($object->getBankFeeAmount());
                                    $bankFeeTransaction->setMoneyOut($object->getBankFeeAmount());
                                    $bankFeeTransaction->setBalanceMainAccount($bankFeeAmountDifference, true);

                                    $this->updateLaterTransactionsBalancesTransaction(
                                        $account,
                                        $bankFeeTransaction,
                                        $bankFeeTransaction->getDate(),
                                        $bankFeeAmountDifference,
                                        true
                                    );
                                }
                            }
                        }
                    }
                /* If there is only change in total amount */
                } else {
                    $accountCurrency = $currency;
                    $invoiceCurrency = $object->getCurrency();

                    if (($accountCurrency !== $invoiceCurrency) && $realAmountPaid) {
                        if ($originalTotalAmount) {
                            $account->setBalance($realAmountPaid, false);
                        }
                    } else {
                        $account->setBalance($amountTotalDifference, false);
                    }

                    $transactions = $object->getTransactions()->toArray();

                    foreach ($transactions as $transaction) {
                        if ($transaction->getTransactionTypeName() == 'Bank payment') {
                            /* If there is a change in date of invoice */
                            if ($originalInvoiceDatePaid !== $datePaid) {
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $transaction,
                                    $transaction->getDate(),
                                    $originalTotalAmount,
                                    true
                                );

                                $lastTransactionBalance = $this->getLastTransactionBalance(
                                    $datePaid,
                                    $account,
                                    $transaction->getTransactionNumber()
                                );

                                $balance = $lastTransactionBalance - $amountTotal;

                                /* Update record in transaction table */
                                $transaction = $this->transactionService->createOrUpdateInvoiceTransaction(
                                    $object,
                                    $datePaid,
                                    $unit,
                                    $bankPaymentTransactionType,
                                    $account,
                                    $currency,
                                    $invoiceCurrency,
                                    $amountTotal,
                                    $userAndDateTime,
                                    $balance,
                                    'update',
                                    $realAmountPaid,
                                    $transaction
                                );

                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $transaction,
                                    $transaction->getDate(),
                                    $amountTotal,
                                    false
                                );
                            } else {
                                $transaction->setDateTimeEdited($dateTime);
                                $transaction->setAmount($amountTotal);
                                $transaction->setMoneyOut($amountTotal);
                                $transaction->setBalanceMainAccount($amountTotalDifference, false);

                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $transaction,
                                    $transaction->getDate(),
                                    $amountTotalDifference,
                                    false
                                );

                                /* BUDGET ACTUAL EXPENSES */
                                if ($object->getBudget()) {
                                    $this->budgetCalculationService
                                        ->calculateBudgetsActualExpenses($object, null, 'update');
                                }
                            }
                        }
                    }
                }
            /* Check if there is a change in bank fee amount */
            } elseif ($object->getBankFeeAmount() != $originalBankFeeAmount) {
                if ($originalBankFeeAmount != null) {
                    foreach ($object->getTransactions() as $transaction) {
                        if ($transaction->getTransactionTypeName() == 'Account Charge') {
                            if (!$object->isBankFeeAdded()) {
                                $invoiceTransaction = $transaction->getTransaction();

                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $transaction,
                                    $transaction->getDate(),
                                    $originalBankFeeAmount,
                                    true
                                );

                                $transactionRepository->remove($transaction, true);

                                $account->setBalance($originalBankFeeAmount, true);

                                $invoiceTransaction->setBankFeeAdded(false);
                                $invoiceTransaction->setBankFeeCurrency(null);
                                $invoiceTransaction->setBankFeeAmount('0');
                            } else {
                                $transaction->setDateTimeEdited($dateTime);
                                $transaction->setAmount($object->getBankFeeAmount());
                                $transaction->setMoneyOut($object->getBankFeeAmount());
                                $transaction->setBalanceMainAccount($bankFeeAmountDifference, true);

                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $transaction,
                                    $transaction->getDate(),
                                    $bankFeeAmountDifference,
                                    true
                                );

                                $account->setBalance($bankFeeAmountDifference, true);
                            }
                        }
                    }
                }
            } else {
                // If there is a change only in Budget (month / year) - from none to yes or vice versa
                $unitOfWork = $this->entityManager->getUnitOfWork();
                $originalData = $unitOfWork->getOriginalEntityData($object);

                if ($this->budgetCalculationService->isValidOriginalData($originalData, 'budget_id')) {
                    if (!$object->getBudget()) {
                        $this->budgetCalculationService
                            ->calculateBudgetsActualExpenses($object, $originalData['budget_id'], 'remove');
                    } elseif (!isset($originalData['budget_id']) || !$originalData['budget_id']) {
                        $this->budgetCalculationService
                            ->calculateBudgetsActualExpenses($object);
                    } else {
                        $this->budgetCalculationService
                            ->calculateBudgetsActualExpenses($object, null, 'update');
                    }
                }
            }

            /* PART-PAYMENTS */
            /* Check if it is Part-Paid before */
            $invoicePartPayments = $object->getInvoicePartPayments()->toArray();
            // $invoicePartPaymentsSnapshot = $object->getInvoicePartPaymentsSnapshot();

            $uow = $this->entityManager->getUnitOfWork();
            $invoicePartPaymentsSnapshot = [];

            if ($invoicePartPaymentsSnapshot) {
                if ($invoicePartPayments !== $invoicePartPaymentsSnapshot) {
                    $this->addOrDeletePartPaymentRows(
                        $object,
                        $account,
                        $currency,
                        $unit,
                        $invoicePartPayments,
                        $invoicePartPaymentsSnapshot
                    );

                    if (empty($invoicePartPayments)) {
                        $object->setPaymentStatus('Unpaid');
                        $object->setRestPaymentTotal($originalTotalAmount);
                    } else {
                        $object->setPaymentStatus('Part-Paid');
                        $object->setRestPaymentTotal($restPaymentTotalNew);
                    }

                    $object->setInvoiceDatePaid(null);
                    $object->setBankFeeAdded(false);

                    if ($object->isBankFeeNotApplicable()) {
                        $object->setBankFeeNotApplicable(false);
                    }
                } else {
                    /* Check if Bank Fee is added/not added to existing Part Payment in Invoice Edit */
                    $allBankFeesAddedInPartPayments = true;

                    foreach ($invoicePartPayments as $partPayment) {
                        if (!$partPayment->isBankFeeAdded() && !$partPayment->isBankFeeNotApplicable()) {
                            $allBankFeesAddedInPartPayments = false;
                        }
                    }

                //    $partPaymentTotalAmount = 0;

                    foreach ($invoicePartPayments as $partPayment) {
                        $originalPartPaymentData = $uow->getOriginalEntityData($partPayment);
                    //    $partPaymentTotalAmount += $partPayment->getAmount();

                        $partPaymentTransaction = $partPayment->getTransaction();
                        $partPaymentBankFeeAmount = $partPayment->getBankFeeAmount();

                        if ($partPaymentTransaction->getTransactions()->toArray()) {
                            $bankFeeTransaction = $partPaymentTransaction->getTransactions()->toArray()[0];
                        } else {
                            $bankFeeTransaction = null;
                        }

                        if ($partPayment->isBankFeeAdded()) {
                            if (!$bankFeeTransaction && !$partPayment->isBankFeeNotApplicable()) {
                                $lastTransactionBalance =
                                    $this->getLastTransactionBalance($partPaymentTransaction->getDate(), $account);
                                $balance = floatval($lastTransactionBalance) - floatval($partPaymentBankFeeAmount);

                                /* Record Bank fee in transaction table */
                                $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                                    $partPayment->getDatePaid(),
                                    $unit,
                                    $bankFeeTransactionType,
                                    $account,
                                    $partPaymentTransaction,
                                    $partPaymentBankFeeAmount,
                                    $userAndDateTime,
                                    $balance,
                                    'create'
                                );

                                $partPaymentTransaction->setBankFeeAdded(true);
                                $partPaymentTransaction->setBankFeeAmount($partPaymentBankFeeAmount);

                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $bankFeeTransaction,
                                    $bankFeeTransaction->getDate(),
                                    $partPaymentBankFeeAmount,
                                    false
                                );

                                $transactionRepository->add($partPaymentTransaction, true);

                                $object->setBankFeeAmount($partPaymentBankFeeAmount, true);
                                $invoiceRepository->add($object, true);

                                $account->setBalance($partPaymentBankFeeAmount, false);

                                if (
                                    $object->getTotalPaid() == $object->getAmount()
                                    && $allBankFeesAddedInPartPayments
                                ) {
                                    $object->setBankFeeAdded(true);
                                }
                            } elseif ($partPayment->isBankFeeNotApplicable()) {
                                $account->setBalance($partPaymentBankFeeAmount, true);

                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $bankFeeTransaction,
                                    $bankFeeTransaction->getDate(),
                                    $partPaymentBankFeeAmount,
                                    true
                                );

                                $object->setBankFeeAmount($partPaymentBankFeeAmount, false);
                                $partPaymentTransaction->setBankFeeAmount($partPaymentBankFeeAmount, false);
                                $partPaymentTransaction->setBankFeeAdded(false);

                                $partPayment->setBankFeeAdded(false);
                                $partPayment->setBankFeeAmount(null);

                                $accountRepository->add($account, true);
                                $invoiceRepository->add($object, true);
                                $invoicePartPaymentRepository->add($partPayment, true);
                                $transactionRepository->add($partPaymentTransaction, true);
                                $transactionRepository->remove($bankFeeTransaction, true);
                            }

                            /* TODO: If user inputs bankFee amount & clicks on Bank Fee N/A simultaneously */
                            // else if ($partPayment->isBankFeeAdded() && $partPayment->isBankFeeNotApplicable()) {
                            //     $partPayment->setBankFeeAdded(false);
                            //     $partPayment->setBankFeeAmount(null);
                            //     $partPayment->isBankFeeNotApplicable(false);
                            // }
                        } elseif ($originalPartPaymentData['bankFeeAmount'] !== $partPayment->getBankFeeAmount()) {
                            $bankFeeAmount = floatval($partPayment->getBankFeeAmount());

                            if ($bankFeeAmount == 0) {
                                if ($partPaymentTransaction->getTransactions()->toArray()) {
                                    $bankFeePartPaymentTransaction = $partPaymentTransaction
                                        ->getTransactions()->toArray()[0];

                                    $account->setBalance($originalPartPaymentData['bankFeeAmount'], true);
                                    $object->setBankFeeAmount($originalPartPaymentData['bankFeeAmount'], false);
                                    $partPaymentTransaction
                                        ->setBankFeeAmount($originalPartPaymentData['bankFeeAmount'], false);
                                    $partPaymentTransaction->setBankFeeAdded(false);

                                    $this->updateLaterTransactionsBalancesTransaction(
                                        $account,
                                        $bankFeePartPaymentTransaction,
                                        $bankFeePartPaymentTransaction->getDate(),
                                        $originalPartPaymentData['bankFeeAmount'],
                                        true
                                    );

                                    $invoiceRepository->add($object, true);
                                    $accountRepository->add($account, true);
                                    $transactionRepository->remove($bankFeeTransaction, true);
                                }
                            }
                        }
                    }

                    /* TODO: Change to part paid if part payment amount is changed,
                        and part payment total amount is less that Invoice Amount */
                    /* Temporary solution added through JavaScript */

                    // if ($partPaymentTotalAmount < $amountTotal) {
                    //     $invoice->setInvoicePaymentStatus('Part-Paid');
                    //     $invoice->setRestPaymentTotal(strval($amountTotal - $partPaymentTotalAmount));
                    //     $invoice->setTotalPaid(strval($partPaymentTotalAmount));
                    // }
                }
            }

            /* Check if it is newly added bank fee amount */
            if (!$invoicePartPayments) {
                if ($object->isBankFeeAdded() && !$originalBankFeeAdded) {
                    $account->setBalance($object->getBankFeeAmount(), false);

                    $transaction = $object->getTransactions()->toArray()[0];

                    $lastTransactionBalance = $this->getLastTransactionBalance($datePaid, $account);
                    $balance = floatval($lastTransactionBalance) - floatval($object->getBankFeeAmount());

                    /* Record Bank fee in transaction table */
                    $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                        $datePaid,
                        $unit,
                        $bankFeeTransactionType,
                        $account,
                        $transaction,
                        $object->getBankFeeAmount(),
                        $userAndDateTime,
                        $balance,
                        'create'
                    );

                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $bankFeeTransaction,
                        $datePaid,
                        $object->getBankFeeAmount(),
                        false
                    );

                    $transaction->setBankFeeAdded(true);
                    $transaction->setBankFeeAmount($object->getBankFeeAmount());
                    $transaction->setBankFeeCurrency($object->getCurrency());

                    if ($object->isBankFeeNotApplicable()) {
                        $object->setBankFeeNotApplicable(false);
                        $transaction->isBankFeeNotApplicable();

                        $invoiceRepository->add($object, true);
                        $transactionRepository->add($transaction, true);
                    }
                }
            }
        } elseif ($paymentStatus == 'Unpaid' && $originalPaymentStatus == 'Paid') {
            // $accountCurrency = $currency;
            $invoiceCurrency = $object->getCurrency();

            $invoiceConnectedTransactions = $object->getTransactions()->toArray();
            $bankPaymentTransaction = null;
            $bankPaymentBankFeeTransaction = null;
            $moneyReturnedTransactions = [];

            foreach ($invoiceConnectedTransactions as $transaction) {
                if ($transaction->getTransactionTypeName() == 'Money Returned') {
                    $moneyReturnedTransactions[] = $transaction;
                }
            }

            $invoicePartPayments = $object->getInvoicePartPayments()->toArray();

            if ($invoicePartPayments) {
                foreach ($invoicePartPayments as $partPayment) {
                    $partPaymentAmount = $partPayment->getAmount();
                    $partPaymentRealAmountPaid = $partPayment->getRealAmountPaid();
                    $partPaymentTransaction = $partPayment->getTransaction();

                    if ($partPaymentRealAmountPaid) {
                        $account->setBalance($partPaymentRealAmountPaid, true);
                        $object->setRealAmountPaid($partPaymentRealAmountPaid, false);
                    } else {
                        $account->setBalance($partPaymentAmount, true);
                    }

                    $object->setRestPaymentTotal($partPaymentAmount, true);
                    $object->setTotalPaid($partPaymentAmount, false);

                    $invoicePartPaymentRepository->remove($partPayment, true);

                    if ($partPaymentTransaction->isBankFeeAdded()) {
                        $account->setBalance($partPaymentTransaction->getBankFeeAmount(), true);

                        $bankFeeTransaction = $partPaymentTransaction->getTransactions()->toArray()[0];

                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $partPaymentTransaction,
                            $partPaymentTransaction->getDate(),
                            $partPaymentTransaction->getAmount(),
                            true
                        );

                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $bankFeeTransaction,
                            $bankFeeTransaction->getDate(),
                            $bankFeeTransaction->getAmount(),
                            true
                        );
                    } else {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $partPaymentTransaction,
                            $partPaymentTransaction->getDate(),
                            $partPaymentTransaction->getAmount(),
                            true
                        );
                    }
                }
            } else {
                foreach ($invoiceConnectedTransactions as $transaction) {
                    if ($transaction->getTransactionTypeName() == 'Bank Payment') {
                        if ($transaction->getMoneyReturnedAmount() == null) {
                            $bankPaymentTransaction = $transaction;

                            if (!empty($transaction->getTransactions()->toArray())) {
                                $bankPaymentBankFeeTransaction = $transaction->getTransactions()->toArray()[0];
                            }
                        }
                    }
                }

                if (($currency !== $invoiceCurrency) && $realAmountPaid) {
                    $object->setRealAmountPaid(null);
                    $object->setRealCurrencyPaid(null);
                    $account->setBalance($realAmountPaid, true);
                } else {
                    $account->setBalance($originalTotalAmount, true);
                }

                if (($currency !== $invoiceCurrency) && $realAmountPaid) {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $bankPaymentTransaction,
                        $bankPaymentTransaction->getDate(),
                        $realAmountPaid,
                        true
                    );
                } else {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $bankPaymentTransaction,
                        $bankPaymentTransaction->getDate(),
                        $bankPaymentTransaction->getAmount(),
                        true
                    );
                }

                /* BUDGET ACTUAL EXPENSES */
                if ($object->getBudget()) {
                    $this->budgetCalculationService->calculateBudgetsActualExpenses($object, null, 'remove');
                }

                $transactionRepository->remove($bankPaymentTransaction, true);

                if ($bankPaymentBankFeeTransaction) {
                    $account->setBalance($bankPaymentBankFeeTransaction->getAmount(), true);

                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $bankPaymentBankFeeTransaction,
                        $bankPaymentBankFeeTransaction->getDate(),
                        $bankPaymentBankFeeTransaction->getAmount(),
                        true
                    );

                    $transactionRepository->remove($bankPaymentBankFeeTransaction, true);
                }
            }

            if (!empty($moneyReturnedTransactions)) {
                foreach ($moneyReturnedTransactions as $moneyReturnedTransaction) {
                    $bankPaymentTransaction = $moneyReturnedTransaction->getTransaction();

                    /* Money returned transaction */
                    $account->setBalance($moneyReturnedTransaction->getAmount(), false);

                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $moneyReturnedTransaction,
                        $moneyReturnedTransaction->getDate(),
                        $moneyReturnedTransaction->getAmount(),
                        false
                    );

                    $transactionRepository->remove($moneyReturnedTransaction, true);

                    /* Connected bank payment transaction */
                    $account->setBalance($bankPaymentTransaction->getAmount(), true);

                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $bankPaymentTransaction,
                        $bankPaymentTransaction->getDate(),
                        $bankPaymentTransaction->getAmount(),
                        true
                    );

                    if ($bankPaymentTransaction->isBankFeeAdded()) {
                        $account->setBalance($bankPaymentTransaction->getBankFeeAmount(), true);
                    }

                    $transactionRepository->remove($bankPaymentTransaction, true);
                }
            }

            $object->setAccount(null);
            $object->setInvoiceDatePaid(null);
            $object->setRealAmountPaid(null);
            $object->setBankFeeAmount('0');
            $object->setRestPaymentTotal($originalTotalAmount);
            $object->setTotalPaid('0');
        } elseif ($paymentStatus == 'Unpaid' && $originalPaymentStatus == 'Part-Paid') {
            $invoicePartPayments = $object->getInvoicePartPayments()->toArray();

            foreach ($invoicePartPayments as $partPayment) {
                $partPaymentAmount = $partPayment->getAmount();
                $partPaymentRealAmountPaid = $partPayment->getRealAmountPaid();
                $partPaymentTransaction = $partPayment->getTransaction();

                if (($currency !== $invoiceCurrency) && $partPaymentRealAmountPaid > 0) {
                    $account->setBalance($partPaymentRealAmountPaid, true);
                    $object->setRealAmountPaid($partPaymentRealAmountPaid, false);
                } else {
                    $account->setBalance($partPaymentAmount, true);
                }

                $object->setRestPaymentTotal($partPaymentAmount, true);
                $object->setTotalPaid($partPaymentAmount, false);

                $invoicePartPaymentRepository->remove($partPayment, true);

                if ($partPaymentTransaction->isBankFeeAdded()) {
                    $account->setBalance($partPaymentTransaction->getBankFeeAmount(), true);

                    $bankFeeTransaction = $partPaymentTransaction->getTransactions()->toArray()[0];

                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $partPaymentTransaction,
                        $partPaymentTransaction->getDate(),
                        $partPaymentTransaction->getAmount(),
                        true
                    );

                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $bankFeeTransaction,
                        $bankFeeTransaction->getDate(),
                        $bankFeeTransaction->getAmount(),
                        true
                    );
                } else {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $partPaymentTransaction,
                        $partPaymentTransaction->getDate(),
                        $partPaymentTransaction->getAmount(),
                        true
                    );
                }
            }

            $object->setAccount(null);
            $object->setBankFeeAdded(false);
            $object->setRealAmountPaid(null);
            $object->setRealCurrencyPaid(null);
            $object->setBankFeeAmount('0');
        } elseif ($paymentStatus == 'Part-Paid') {
            $uow = $this->entityManager->getUnitOfWork();

            $invoicePartPayments = $object->getInvoicePartPayments()->toArray();
            // $invoicePartPaymentsSnapshot = $object->getInvoicePartPaymentsSnapshot();
            $invoicePartPaymentsSnapshotItems = [];

            // foreach ($invoicePartPaymentsSnapshot as $partPayment) {
            //     if ($partPayment->getMoneyReturnedAmount() == null && $partPayment->getMoneyReturnedDate() == null) {
            //         $invoicePartPaymentsSnapshotItems[] = $partPayment;
            //     }
            // }

            if ($originalPaymentStatus == 'Paid') {
                /* Reset data from Paid status */
                $account->setBalance($amountTotal, true);
                $bankFeeAmount = $object->getBankFeeAmount();

                if ($bankFeeAmount > 0) {
                    $account->setBalance($bankFeeAmount, true);
                    $object->setBankFeeAmount('0');
                }

                $accountRepository->add($account);

                $object->setInvoiceDatePaid(null);
                $object->setRestPaymentTotal($originalTotalAmount);
                $object->setTotalPaid('0');

                $invoiceTransactions = $object->getTransactions()->toArray();

                /* Delete invoice transactions and bank fee transaction */
                foreach ($invoiceTransactions as $transaction) {
                    $transactionRepository->remove($transaction, true);
                }

                foreach ($invoiceTransactions as $transaction) {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $transaction,
                        $transaction->getDate(),
                        $transaction->getAmount(),
                        true
                    );
                }

                /* Calculate in the Part-Payments from scratch */
                $restPaymentTotalCurrent = $object->getRestPaymentTotal();

                foreach ($invoicePartPayments as $partPayment) {
                    $amount = $partPayment->getAmount();

                    $account->setBalance($amount, false);
                    $object->setTotalPaid($amount, true);

                    $restPaymentTotalCurrent -= $amount;

                    $partPayment->getRestPaymentAmount();
                    $partPayment->setCurrency($currency);
                    $invoicePartPaymentRepository->add($partPayment);

                    $lastTransactionBalance =
                        $this->getLastTransactionBalance($partPayment->getDatePaid(), $account);
                    $balance = floatval($lastTransactionBalance) - floatval($amount);

                    /* New record in transaction table */
                    $partPaymentTransaction = $this->transactionService->createOrUpdateInvoiceTransaction(
                        $object,
                        $partPayment->getDatePaid(),
                        $unit,
                        $bankPaymentTransactionType,
                        $account,
                        $currency,
                        $invoiceCurrency,
                        $amount,
                        $userAndDateTime,
                        $balance,
                        'create',
                        $realAmountPaid
                    );

                    $partPayment->setTransaction($partPaymentTransaction);

                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $partPaymentTransaction,
                        $partPayment->getDatePaid(),
                        $amount,
                        false
                    );

                    if ($partPayment->isBankFeeAdded()) {
                        $bankFeeAmount = $partPayment->getBankFeeAmount();

                        $lastTransactionBalance =
                            $this->getLastTransactionBalance($partPaymentTransaction->getDate(), $account);
                        $balance = floatval($lastTransactionBalance) - floatval($bankFeeAmount);

                        /* New record in transaction table */
                        $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                            $partPayment->getDatePaid(),
                            $unit,
                            $bankFeeTransactionType,
                            $account,
                            $partPaymentTransaction,
                            $bankFeeAmount,
                            $userAndDateTime,
                            $balance,
                            'create'
                        );

                        $partPaymentTransaction->setBankFeeAdded(true);
                        $partPaymentTransaction->setBankFeeAmount($bankFeeAmount);

                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $bankFeeTransaction,
                            $bankFeeTransaction->getDate(),
                            $bankFeeAmount,
                            false
                        );

                        $transactionRepository->add($partPaymentTransaction, true);

                        $object->setBankFeeAmount($bankFeeAmount, true);
                        $invoiceRepository->add($object, true);

                        $account->setBalance($bankFeeAmount, false);
                    }

                    if ($object->getTotalPaid() == $object->getAmount()) {
                        $object->setPaymentStatus('Paid');
                        $object->setInvoiceDatePaid($partPaymentTransaction->getDate());

                        /* TODO: Refactor, test solution */
                        $allBankFeesAddedInPartPayments = true;

                        foreach ($invoicePartPayments as $partPayment) {
                            if (!$partPayment->isBankFeeAdded()) {
                                $allBankFeesAddedInPartPayments = false;
                            }
                        }

                        // Check if all bank fees are added using array_reduce
//                        $allBankFeesAddedInPartPayments = array_reduce(
//                            $invoicePartPayments,
//                            function ($carry, $partPayment) {
//                                return $carry && $partPayment->isBankFeeAdded();
//                            },
//                            true
//                        );

                        if ($allBankFeesAddedInPartPayments) {
                            $object->setBankFeeAdded(true);
                        }
                    }
                }
            } else {
                /* Check if Bank Fee is added to existing Part Payment in Invoice Edit */
                $allBankFeesAddedInPartPayments = true;

                foreach ($invoicePartPayments as $partPayment) {
                    if (!$partPayment->isBankFeeAdded() && !$partPayment->isBankFeeNotApplicable()) {
                        $allBankFeesAddedInPartPayments = false;
                    }
                }

                /* If Part-payment(s) is/are deleted */
                if ($invoicePartPayments !== $invoicePartPaymentsSnapshotItems) {
                    $this->addOrDeletePartPaymentRows(
                        $object,
                        $account,
                        $currency,
                        $unit,
                        $invoicePartPayments,
                        $invoicePartPaymentsSnapshotItems
                    );

                    /* Creating Bank Fee Transaction for new Part-payments if Bank Fee is added */
                    foreach ($invoicePartPayments as $partPayment) {
                        $partPaymentTransaction = $partPayment->getTransaction();
                        $partPaymentBankFeeAmount = $partPayment->getBankFeeAmount();

                        if ($partPaymentTransaction->getTransactions()->toArray()) {
                            $bankFeeTransaction = $partPaymentTransaction->getTransactions()->toArray()[0];
                        } else {
                            $bankFeeTransaction = null;
                        }

                        if ($partPayment->isBankFeeAdded()) {
                            if (!$bankFeeTransaction) {
                                $lastTransactionBalance =
                                    $this->getLastTransactionBalance($partPaymentTransaction->getDate(), $account);
                                $balance = floatval($lastTransactionBalance) - floatval($partPaymentBankFeeAmount);

                                /* New record in transaction table */
                                $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                                    $partPayment->getDatePaid(),
                                    $unit,
                                    $bankFeeTransactionType,
                                    $account,
                                    $partPaymentTransaction,
                                    $partPaymentBankFeeAmount,
                                    $userAndDateTime,
                                    $balance,
                                    'create'
                                );

                                $partPaymentTransaction->setBankFeeAdded(true);
                                $partPaymentTransaction->setBankFeeAmount($partPaymentBankFeeAmount);

                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $bankFeeTransaction,
                                    $bankFeeTransaction->getDate(),
                                    $partPaymentBankFeeAmount,
                                    false
                                );

                                $transactionRepository->add($partPaymentTransaction, true);

                                $object->setBankFeeAmount($partPaymentBankFeeAmount, true);
                                $invoiceRepository->add($object, true);

                                $account->setBalance($partPaymentBankFeeAmount, false);

                                if (
                                    $object->getTotalPaid() == $object->getAmount()
                                    && $allBankFeesAddedInPartPayments
                                ) {
                                    $object->setBankFeeAdded(true);
                                }

                                if ($partPayment->isBankFeeAdded() && $partPayment->isBankFeeNotApplicable()) {
                                    $partPayment->isBankFeeNotApplicable();
                                }
                            }
                        }
                    }

                    if (!$invoicePartPayments) {
                        $object->setAccount(null);
                        $object->setRealAmountPaid(null);
                        $object->setPaymentStatus('Unpaid');
                        $object->setTotalPaid('0');
                        $object->setBankFeeAmount('0');
                    }
                /* Checking for date/value/bankFee changes in existing Part-payment(s) */
                /* NOTE: editing in invoice form temporarily disabled */
                } else {
                    foreach ($invoicePartPayments as $partPayment) {
                        $originalPartPaymentData = $uow->getOriginalEntityData($partPayment);
                        $partPaymentTransaction = $partPayment->getTransaction();

                        /* Bank Fee and Date/Amount */
                        if ($originalPartPaymentData['bankFeeAmount'] !== $partPayment->getBankFeeAmount()) {
                            if (
                                !(is_null($originalPartPaymentData['bankFeeAmount'])
                                && $partPayment->getBankFeeAmount() == 0)
                            ) {
                                $originalBankFeeAmount = $originalPartPaymentData['bankFeeAmount'];
                                $bankFeeAmount = $partPayment->getBankFeeAmount();

                                if ($bankFeeAmount == 0 || $bankFeeAmount == null) {
                                    $bankFeeTransaction = $transactionRepository
                                        ->findOneBy(['transaction' => $partPaymentTransaction]);

                                    $this->updateLaterTransactionsBalancesTransaction(
                                        $account,
                                        $bankFeeTransaction,
                                        $bankFeeTransaction->getDate(),
                                        $originalBankFeeAmount,
                                        true
                                    );

                                    $transactionRepository->remove($bankFeeTransaction, true);

                                    $account->setBalance($originalBankFeeAmount, true);

                                    $partPaymentTransaction->setBankFeeAdded(false);
                                    $partPaymentTransaction->setBankFeeAmount(null);
                                    $object->setBankFeeAmount($originalBankFeeAmount, false);

                                    if ($object->isBankFeeAdded()) {
                                        $object->setBankFeeAdded(false);
                                    }
                                } else {
                                    $differenceAmount = $bankFeeAmount - $originalBankFeeAmount;
                                    $bankFeeTransaction = $transactionRepository
                                        ->findOneBy(['transaction' => $partPaymentTransaction]);

                                    if ($bankFeeTransaction) {
                                        $this->updateLaterTransactionsBalancesTransaction(
                                            $account,
                                            $bankFeeTransaction,
                                            $bankFeeTransaction->getDate(),
                                            $originalBankFeeAmount,
                                            true
                                        );

                                        $account->setBalance($originalBankFeeAmount, true);

                                        $lastTransactionBalance = $this->getLastTransactionBalance(
                                            $bankFeeTransaction->getDate(),
                                            $account,
                                            $bankFeeTransaction->getTransactionNumber()
                                        );

                                        $balance = floatval($lastTransactionBalance) - floatval($bankFeeAmount);
                                        $bankFeeTransaction->setBalanceMainAccount(strval($balance));
                                        $bankFeeTransaction->setMoneyOut($bankFeeAmount);
                                        $bankFeeTransaction->setAmount($bankFeeAmount);

                                        $this->updateLaterTransactionsBalancesTransaction(
                                            $account,
                                            $bankFeeTransaction,
                                            $bankFeeTransaction->getDate(),
                                            $bankFeeAmount,
                                            false
                                        );

                                        $account->setBalance($bankFeeAmount, false);

                                        $partPaymentTransaction->setBankFeeAmount($bankFeeAmount);
                                        $object->setBankFeeAmount(strval($differenceAmount), true);
                                    } else {
                                        $lastTransactionBalance = $this->getLastTransactionBalance(
                                            $partPaymentTransaction->getDate(),
                                            $account
                                        );

                                        $balance = floatval($lastTransactionBalance) - floatval($bankFeeAmount);

                                        /* New record in transaction table */
                                        $bankFeeTransaction =
                                            $this->transactionService->createOrUpdateBankFeeTransaction(
                                                $partPayment->getDatePaid(),
                                                $unit,
                                                $bankFeeTransactionType,
                                                $account,
                                                $partPaymentTransaction,
                                                $bankFeeAmount,
                                                $userAndDateTime,
                                                $balance,
                                                'create'
                                            );

                                        $partPaymentTransaction->setBankFeeAdded(true);
                                        $partPaymentTransaction->setBankFeeAmount($bankFeeAmount);

                                        $this->updateLaterTransactionsBalancesTransaction(
                                            $account,
                                            $bankFeeTransaction,
                                            $bankFeeTransaction->getDate(),
                                            $bankFeeAmount,
                                            false
                                        );

                                        $transactionRepository->add($partPaymentTransaction, true);

                                        $object->setBankFeeAmount($bankFeeAmount, true);
                                        $invoiceRepository->add($object, true);

                                        if (
                                            $object->getTotalPaid() == $object->getAmount()
                                            && $allBankFeesAddedInPartPayments
                                        ) {
                                            $object->setBankFeeAdded(true);
                                        }

                                        $account->setBalance($bankFeeAmount, false);
                                    }
                                }
                            }
                        /* Date Paid */
                        } elseif (
                            $originalPartPaymentData['datePaid']->format('Y-m-d')
                            !== $partPayment->getDatePaid()->format('Y-m-d')
                        ) {
                            $originalDatePaid = $originalPartPaymentData['datePaid'];
                            $datePaid = $partPayment->getDatePaid();

                            if (floatval($originalPartPaymentData['amount']) !== floatval($partPayment->getAmount())) {
                                $originalAmount = $originalPartPaymentData['amount'];
                                $amount = $partPayment->getAmount();

                                $differenceAmount = strval($amount - $originalAmount);

                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $partPaymentTransaction,
                                    $originalDatePaid,
                                    $originalAmount,
                                    true
                                );

                                $account->setBalance($originalAmount, true);

                                $lastTransactionBalance = $this->getLastTransactionBalance(
                                    $datePaid,
                                    $account,
                                    $partPaymentTransaction->getTransactionNumber()
                                );
                                $balance = floatval($lastTransactionBalance) - floatval($amount);
                                $partPaymentTransaction->setDate($datePaid);
                                $partPaymentTransaction->setBalanceMainAccount(strval($balance));
                                $partPaymentTransaction->setMoneyOut($amount);
                                $partPaymentTransaction->setAmount($amount);

                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $partPaymentTransaction,
                                    $datePaid,
                                    $amount,
                                    false
                                );

                                $account->setBalance($amount, false);

                                $partPayment->getRestPaymentAmount($differenceAmount, false);

                                $object->setRestPaymentTotal($differenceAmount, false);
                                $object->setTotalPaid($differenceAmount, true);
                            } else {
                                $amount = $partPayment->getAmount();

                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $partPaymentTransaction,
                                    $originalDatePaid,
                                    $amount,
                                    true
                                );

                                $lastTransactionBalance = $this->getLastTransactionBalance(
                                    $datePaid,
                                    $account,
                                    $partPaymentTransaction->getTransactionNumber()
                                );

                                $balance = floatval($lastTransactionBalance) - floatval($amount);

                                $partPaymentTransaction->setDate($datePaid);
                                $partPaymentTransaction->setBalanceMainAccount(strval($balance));
                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $partPaymentTransaction,
                                    $datePaid,
                                    $amount,
                                    false
                                );
                            }

                            if ($partPayment->isBankFeeAdded()) {
                                $transactionRepository->add($partPaymentTransaction, true);

                                $bankFeeAmount = $partPayment->getBankFeeAmount();
                                $bankFeeTransaction = $partPaymentTransaction->getTransactions()->toArray()[0];

                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $bankFeeTransaction,
                                    $originalDatePaid,
                                    $bankFeeAmount,
                                    true
                                );

                                $lastTransactionBalance = $this->getLastTransactionBalance(
                                    $datePaid,
                                    $account,
                                    $bankFeeTransaction->getTransactionNumber()
                                );
                                $balance = floatval($lastTransactionBalance) - floatval($bankFeeAmount);
                                $bankFeeTransaction->setDate($datePaid);
                                $bankFeeTransaction->setBalanceMainAccount(strval($balance));

                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $bankFeeTransaction,
                                    $datePaid,
                                    $bankFeeAmount,
                                    false
                                );
                            }
                            /* Amount */
                        } elseif (
                            floatval($originalPartPaymentData['amount'])
                            !== floatval($partPayment->getAmount())
                        ) {
                            $originalAmount = $originalPartPaymentData['amount'];
                            $amount = $partPayment->getAmount();

                            $differenceAmount = strval($amount - $originalAmount);

                            $this->updateLaterTransactionsBalancesTransaction(
                                $account,
                                $partPaymentTransaction,
                                $partPaymentTransaction->getDate(),
                                $originalAmount,
                                true
                            );

                            $account->setBalance($originalAmount, true);

                            $lastTransactionBalance = $this->getLastTransactionBalance(
                                $partPaymentTransaction->getDate(),
                                $account,
                                $partPaymentTransaction->getTransactionNumber()
                            );
                            $balance = floatval($lastTransactionBalance) - floatval($amount);
                            $partPaymentTransaction->setBalanceMainAccount(strval($balance));
                            $partPaymentTransaction->setMoneyOut($amount);
                            $partPaymentTransaction->setAmount($amount);

                            $this->updateLaterTransactionsBalancesTransaction(
                                $account,
                                $partPaymentTransaction,
                                $partPaymentTransaction->getDate(),
                                $amount,
                                false
                            );

                            $account->setBalance($amount, false);

                            $partPayment->getRestPaymentAmount($differenceAmount, false);

                            $object->setRestPaymentTotal($differenceAmount, false);
                            $object->setTotalPaid($differenceAmount, true);
                            /* TODO: refactor this in the one function in TRAIT */
                            if (
                                $partPayment->isBankFeeNotApplicable()
                                && !$originalPartPaymentData['bankFeeNotAdded']
                            ) {
                                $bankFeeTransaction = $partPaymentTransaction->getTransactions()->toArray()[0];
                                $partPaymentBankFeeAmount = $bankFeeTransaction->getAmount();

                                $account->setBalance($partPaymentBankFeeAmount, true);

                                $this->updateLaterTransactionsBalancesTransaction(
                                    $account,
                                    $bankFeeTransaction,
                                    $bankFeeTransaction->getDate(),
                                    $partPaymentBankFeeAmount,
                                    true
                                );

                                $object->setBankFeeAmount($partPaymentBankFeeAmount, false);
                                $partPaymentTransaction->setBankFeeAmount($partPaymentBankFeeAmount, false);
                                $partPaymentTransaction->setBankFeeAdded(false);

                                $partPayment->setBankFeeAdded(false);
                                $partPayment->setBankFeeAmount(null);

                                $accountRepository->add($account, true);
                                $invoiceRepository->add($object, true);
                                $invoicePartPaymentRepository->add($partPayment, true);
                                $transactionRepository->add($partPaymentTransaction, true);
                                $transactionRepository->remove($bankFeeTransaction, true);
                            }
                        } elseif ($partPayment->isBankFeeNotApplicable()) {
                            $partPaymentTransactions = $partPaymentTransaction->getTransactions()->toArray();
                            $partPaymentTransaction->isBankFeeNotApplicable(true);

                            if (!empty($partPaymentTransactions)) {
                                $bankFeeTransaction = $partPaymentTransaction->getTransactions()->toArray()[0];

                                if ($bankFeeTransaction->getTransactionTypeName() == 'Account Charge') {
                                    $partPaymentBankFeeAmount = $bankFeeTransaction->getAmount();

                                    $account->setBalance($partPaymentBankFeeAmount, true);

                                    $this->updateLaterTransactionsBalancesTransaction(
                                        $account,
                                        $bankFeeTransaction,
                                        $bankFeeTransaction->getDate(),
                                        $partPaymentBankFeeAmount,
                                        true
                                    );

                                    $object->setBankFeeAmount($partPaymentBankFeeAmount, false);
                                    $partPaymentTransaction->setBankFeeAdded(false);

                                    $partPayment->setBankFeeAdded(false);
                                    $partPayment->setBankFeeAmount(null);

                                    $accountRepository->add($account, true);
                                    $invoiceRepository->add($object, true);
                                    $invoicePartPaymentRepository->add($partPayment, true);
                                    $transactionRepository->add($partPaymentTransaction, true);
                                    $transactionRepository->remove($bankFeeTransaction, true);
                                }
                            }
                        } elseif (!$partPayment->isBankFeeNotApplicable()) {
                            $partPaymentTransaction->isBankFeeNotApplicable(false);
                        }

                        if ($object->getTotalPaid() == $object->getAmount()) {
                            $object->setPaymentStatus('Paid');
                            $object->setInvoiceDatePaid($partPayment->getDatePaid());
                        }
                    }
                }
            }

            $object->setRestPaymentTotal(strval($restPaymentTotalNew));
        }

        $object->setEditedByUser($user);
        $object->setEditedByUserDeleted(null);
        $object->setDateTimeEdited($dateTime);
        $object->setCurrency($invoiceCurrency);
        $object->setAmount($amountTotal);

        $invoiceTransactions = $object->getTransactions();

        /* Update connected transaction */
        foreach ($invoiceTransactions as $invoiceTransaction) {
            if ($invoiceTransaction->getTransactionTypeName() == 'Bank Payment') {
                $invoiceTransaction->setDateTimeEdited($dateTime);
                // $invoiceTransaction->setDepartment($object->getDepartment());

                if (!$invoiceTransaction->getInvoicePartPayment()) {
                    $invoiceTransaction->setDescription($object->getDescription());
                }

                // $invoiceTransaction->setBudget($object->getBudget());
            }
        }

        // /* Adding comment(s) management */
        // $this->manageEmbeddedCommentAdmin($object);
    }

    // MARK: - PreRemove
    protected function preRemove(object $object): void
    {
        /** @var Invoice $object */
        /** @var InvoicePartPaymentRepository $invoicePartPaymentRepository */

        $invoicePartPaymentRepository = $this->entityManager->getRepository(InvoicePartPayment::class);
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        $flag = 'remove';
        $account = $object->getAccount();
        $invoiceCurrency = $object->getCurrency();

        $realAmountPaid = $object->getRealAmountPaid();

        $invoiceConnectedTransactions = $object->getTransactions()->toArray();

        if ($object->getPaymentStatus() == 'Paid' || $object->getPaymentStatus() == 'Unpaid') {
            /* Transaction balances update before removing */
            $invoiceTransactions = [];
            $bankFeeTransactions = [];
            $moneyReturnedTransactions = [];

            foreach ($invoiceConnectedTransactions as $transaction) {
                if ($transaction->getTransactionTypeName() == 'Bank Payment') {
                    $invoiceTransactions[] = $transaction;
                } elseif ($transaction->getTransactionTypeName() == 'Account Charge') {
                    $bankFeeTransactions[] = $transaction;
                } elseif ($transaction->getTransactionTypeName() == 'Money Returned') {
                    $moneyReturnedTransactions[] = $transaction;
                }
            }

            foreach ($moneyReturnedTransactions as $moneyReturnedTransaction) {
                if ($object->getPaymentStatus() == 'Unpaid') {
                    $account = $moneyReturnedTransaction->getMainAccount();
                }

                $this->updateLaterTransactionsBalancesTransaction(
                    $account,
                    $moneyReturnedTransaction,
                    $moneyReturnedTransaction->getDate(),
                    $moneyReturnedTransaction->getAmount(),
                    false
                );

                $account->setBalance($moneyReturnedTransaction->getAmount(), false);
            }

            foreach ($bankFeeTransactions as $bankFeeTransaction) {
                if ($object->getPaymentStatus() == 'Unpaid') {
                    $account = $bankFeeTransaction->getMainAccount();
                }

                $this->updateLaterTransactionsBalancesTransaction(
                    $account,
                    $bankFeeTransaction,
                    $bankFeeTransaction->getDate(),
                    $bankFeeTransaction->getAmount(),
                    true
                );

                $account->setBalance($bankFeeTransaction->getAmount(), true);
            }

            foreach ($invoiceTransactions as $transaction) {
                $account = $transaction->getMainAccount();
                $accountCurrency = $account->getCurrency();

                if ($object->getPaymentStatus() == 'Unpaid') {
                    if ($invoiceCurrency !== $accountCurrency) {
                        $realAmountPaid = $transaction->getAmount();
                    }
                }

                if (($invoiceCurrency !== $accountCurrency) && $realAmountPaid) {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $transaction,
                        $transaction->getDate(),
                        $realAmountPaid,
                        true
                    );

                    $account->setBalance($realAmountPaid, true);
                } else {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $transaction,
                        $transaction->getDate(),
                        $transaction->getAmount(),
                        true
                    );

                    $account->setBalance($transaction->getAmount(), true);
                }
            }

            /* Remove transactions */
            foreach ($moneyReturnedTransactions as $moneyReturnedTransaction) {
                $transactionRepository->remove($moneyReturnedTransaction, true);
            }

            foreach ($bankFeeTransactions as $bankFeeTransaction) {
                $transactionRepository->remove($bankFeeTransaction, true);
            }

            foreach ($invoiceTransactions as $transaction) {
                $transactionRepository->remove($transaction, true);
            }

            if ($object->getPaymentStatus() == 'Paid') {
                /* BUDGET ACTUAL EXPENSES */
                if ($object->getBudget()) {
                    $this->budgetCalculationService->calculateBudgetsActualExpenses($object, null, $flag);
                }
            }
        } elseif ($object->getPaymentStatus() == 'Part-Paid') {
            $invoicePartPayments = $object->getInvoicePartPayments()->toArray();

            foreach ($invoicePartPayments as $partPayment) {
                if ($partPayment->getMoneyReturnedAmount() == null) {
                    $partPaymentAmount = $partPayment->getAmount();
                    $partPaymentTransaction = $partPayment->getTransaction();

                    $account->setBalance($partPaymentAmount, true);

                    $invoicePartPaymentRepository->remove($partPayment, true);

                    if ($partPaymentTransaction->isBankFeeAdded()) {
                        $account->setBalance($partPaymentTransaction->getBankFeeAmount(), true);

                        $bankFeeTransaction = $partPaymentTransaction->getTransactions()->toArray()[0];

                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $partPaymentTransaction,
                            $partPaymentTransaction->getDate(),
                            $partPaymentTransaction->getAmount(),
                            true
                        );

                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $bankFeeTransaction,
                            $bankFeeTransaction->getDate(),
                            $bankFeeTransaction->getAmount(),
                            true
                        );
                    } else {
                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $partPaymentTransaction,
                            $partPaymentTransaction->getDate(),
                            $partPaymentTransaction->getAmount(),
                            true
                        );
                    }
                } else {
                    $partPaymentTransaction = $partPayment->getTransaction();

                    if ($partPaymentTransaction->isBankFeeAdded()) {
                        $account->setBalance($partPaymentTransaction->getBankFeeAmount(), true);

                        $bankFeeTransaction = $partPaymentTransaction->getTransactions()->toArray()[0];

                        $this->updateLaterTransactionsBalancesTransaction(
                            $account,
                            $bankFeeTransaction,
                            $bankFeeTransaction->getDate(),
                            $bankFeeTransaction->getAmount(),
                            true
                        );
                    }
                }
            }
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
        //         ->orderBy($rootAlias . '.invoiceDate', 'DESC')
        //         ->addOrderBy($rootAlias . '.dateTimeAdded', 'DESC')
        //         ->setParameter('unitId', $unitId)
        //     ;
        // } else {
            $qb
                ->join($rootAlias . '.unit', 'u')
                ->andWhere('u.active = :active')
                ->orderBy($rootAlias . '.invoiceDate', 'DESC')
                ->addOrderBy($rootAlias . '.dateTimeAdded', 'DESC')
                ->setParameter('active', true)
            ;
        // }

        return $query;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::SORT_BY] = 'invoiceDate';
    }

    /**
     * @return array<string, mixed>
     */
    private function getDataForCreateAndUpdate(Invoice $invoice): array
    {
        /* Get original data from database before update */
        $originalData = $this->getOriginalData($invoice);

        /* Get logged in user from the token */
        $user = $this->tokenStorage->getToken()->getUser();

        /* Get actual dateTime */
        $dateTime = new DateTime();

        /* Get account */
        $account = $invoice->getAccount();

        /* Get currency */
        if ($invoice->getCurrency()) {
            if ($account !== null) {
                if ($account->getCurrency() == $invoice->getCurrency()) {
                    $currency = $invoice->getCurrency();
                } else {
                    $currency = $account->getCurrency();
                }
            } else {
                $currency = $invoice->getCurrency();
            }
        } else {
            $currency = $account->getCurrency();
        }

        /* Get approval status */
        $approvalStatus = $invoice->getApprovalStatus();

        /* Get payment status */
        $paymentStatus = $invoice->getPaymentStatus();

        /* Get lineTotals and calculate into the $amountTotal */
        $invoiceLines = $invoice->getInvoiceLines();
        $amountTotal = 0;

        foreach ($invoiceLines as $invoiceLine) {
            $lineTotal = $invoiceLine->getLineTotal();
            $amountTotal += $lineTotal;
        }

        $realAmountPaid = $invoice->getRealAmountPaid();

        if ($originalData) {
            $originalRealAmountPaid = $originalData['realAmountPaid'];
        } else {
            $originalRealAmountPaid = null;
        }

        $restPaymentTotal = $amountTotal;
        $totalPartPaymentAmount = 0;

        /* Get down payments and calculate into the $amountTotal */
        if ($invoice->getInvoicePartPayments()->toArray()) {
            $partPayments = $invoice->getInvoicePartPayments()->toArray();
            $partPaymentsArr = [];

            if ($partPayments) {
                foreach ($partPayments as $partPayment) {
                    if (
                        $partPayment->getMoneyReturnedAmount() == null
                        && $partPayment->getMoneyReturnedDate() == null
                    ) {
                        $partPaymentId = $partPayment->getId();
                        $partPaymentAmountLineDatePaid = $partPayment->getDatePaid();
                        $partPaymentAmountLine = $partPayment->getAmount();
                        $totalPartPaymentAmount += $partPaymentAmountLine;

                        $partPaymentArr = [
                            'id' =>  $partPaymentId,
                            'datePaid' => $partPaymentAmountLineDatePaid,
                            'amount' => $partPaymentAmountLine
                        ];

                        $partPaymentsArr[] = $partPaymentArr;
                    }
                }

                if ($totalPartPaymentAmount > 0) {
                    $restPaymentTotal = $amountTotal - $totalPartPaymentAmount;
                }
            }
        } elseif ($paymentStatus == 'Paid') {
            $restPaymentTotal = 0;
        }

        if (isset($originalData['invoiceDate'])) {
            $originalInvoiceDate = $originalData['invoiceDate'];
        }

        if (isset($originalData['invoiceDatePaid'])) {
            $originalInvoiceDatePaid = $originalData['invoiceDatePaid'];
        } else {
            if ($paymentStatus == 'Paid') {
                $originalInvoiceDatePaid = $originalData['invoiceDate'] ?? null;
            } else {
                $originalInvoiceDatePaid = null;
            }
        }

        $currentAmount = $invoice->getAmount();

        $originalBankFeeAmount = $originalData['bankFeeAmount'] ?? null;
        $currentBankFeeAmount = $invoice->getBankFeeAmount();

        $data = [];

        $data['user'] = $user;
        $data['dateTime'] = $dateTime;
        $data['account'] = $account;
        $data['currency'] = $currency;
        $data['approvalStatus'] = $approvalStatus;
        $data['paymentStatus'] = $paymentStatus;
        $data['originalPaymentStatus'] = $originalData['invoicePaymentStatus'] ?? null;
        $data['partPaymentsArr'] = $partPaymentsArr ?? null;
        $data['totalPartPaymentAmount'] = strval($totalPartPaymentAmount) ?? null;
        $data['restPaymentTotal'] = strval($restPaymentTotal) ?? null;
        $data['amountTotal'] = strval($amountTotal);
        $data['amountTotalDifference'] = strval($amountTotal - $currentAmount);
        $data['realAmountPaid'] = strval($realAmountPaid);
        $data['originalRealAmountPaid'] = strval($originalRealAmountPaid);
        $data['realAmountPaidDifference'] = strval($realAmountPaid - $originalRealAmountPaid);
        $data['originalAccount'] = $originalData['account'] ?? null;
        $data['originalCurrency'] = $originalData['currency'] ?? null;
        $data['originalTotalAmount'] = $currentAmount ?? null;
        $data['originalBankFeeAmount'] = $originalData['bankFeeAmount'] ?? null;
        $data['originalBankFeeAdded'] = $originalData['bankFeeAdded'] ?? null;
        $data['originalInvoiceDate'] = $originalInvoiceDate ?? null;
        $data['originalInvoiceDatePaid'] = $originalInvoiceDatePaid ?? null;
        $data['bankFeeAmountDifference'] = strval($originalBankFeeAmount - $currentBankFeeAmount);

        return $data;
    }

    public function getOriginalData(Invoice $invoice): array
    {
        return $this->entityManager->getUnitOfWork()->getOriginalEntityData($invoice);
    }

    /**
     * @throws Exception
     */
    private function addOrDeletePartPaymentRows(
        Invoice $invoice,
        Account $account,
        string $currency,
        Unit $unit,
        array $invoicePartPayments,
        array $invoicePartPaymentsSnapshotItems = []
    ): void {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);
        /** @var InvoicePartPaymentRepository $invoicePartPaymentRepository */
        $invoicePartPaymentRepository = $this->entityManager->getRepository(InvoicePartPayment::class);

        $userAndDateTime = $this->getUserAndDateTime();
        $bankPaymentTransactionType = TransactionType::BANK_PAYMENT;

        $invoiceCurrency = $invoice->getCurrency();
        $accountCurrency = $account->getCurrency();

        $deletedPartPaymentsRows = [];

        foreach ($invoicePartPaymentsSnapshotItems as $snapshotItem) {
            $found = false;

            foreach ($invoicePartPayments as $item) {
                if ($item->getId() == $snapshotItem->getId()) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $deletedPartPaymentsRows[] = $snapshotItem;
            }
        }

        if (!$deletedPartPaymentsRows) {
            $invoiceAmount = 0;

            if ($invoice->getRestPaymentTotal() == null) {
                foreach ($invoice->getInvoiceLines() as $line) {
                    $invoiceAmount += $line->getLineTotal();
                }

                $restPaymentTotalCurrent =  $invoiceAmount;
            } else {
                $restPaymentTotalCurrent = $invoice->getRestPaymentTotal();
            }

            $newPartPayments = [];
            $collectionOrderNumbers = [];

            foreach ($invoicePartPayments as $partPayment) {
                if (!$partPayment->getId()) {
                    $newPartPayments[] = $partPayment;
                } else {
                    $collectionOrderNumbers[] = $partPayment->getCollectionOrderNumber();
                }
            }

            if ($collectionOrderNumbers) {
                $lastCollectionOrderNumber = max($collectionOrderNumbers);
            } else {
                $lastCollectionOrderNumber = 0;
            }

            foreach ($newPartPayments as $partPayment) {
                $amount = $partPayment->getAmount();
                $realAmountPaid = $partPayment->getRealAmountPaid();

                if ($realAmountPaid) {
                    $account->setBalance($realAmountPaid, false);
                    $invoice->setRealAmountPaid($realAmountPaid, true);
                    $invoice->setRealCurrencyPaid($accountCurrency);
                    $partPayment->setRealCurrency($accountCurrency);
                } else {
                    $account->setBalance($amount, false);
                }

                $invoice->setTotalPaid($amount, true);

                $restPaymentTotalCurrent -= $amount;
                $lastCollectionOrderNumber++;

                $partPayment->getRestPaymentAmount(strval($restPaymentTotalCurrent));
                $partPayment->setCurrency($invoiceCurrency);
                $partPayment->setCollectionOrderNumber($lastCollectionOrderNumber);
                $invoicePartPaymentRepository->add($partPayment);

                $lastTransactionBalance =
                    $this->getLastTransactionBalance($partPayment->getDatePaid(), $account);

                if ($realAmountPaid) {
                    $balance = $lastTransactionBalance - $realAmountPaid;
                } else {
                    $balance = $lastTransactionBalance - $amount;
                }

                /* New record in transaction table */
                $invoicePartPaymentTransaction = $this->transactionService->createOrUpdateInvoiceTransaction(
                    $invoice,
                    $partPayment->getDatePaid(),
                    $unit,
                    $bankPaymentTransactionType,
                    $account,
                    $currency,
                    $invoiceCurrency,
                    $amount,
                    $userAndDateTime,
                    $balance,
                    'create',
                    $realAmountPaid
                );

                $partPayment->setTransaction($invoicePartPaymentTransaction);

                if ($realAmountPaid) {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $invoicePartPaymentTransaction,
                        $partPayment->getDatePaid(),
                        $realAmountPaid,
                        false
                    );
                } else {
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $invoicePartPaymentTransaction,
                        $partPayment->getDatePaid(),
                        $amount,
                        false
                    );
                }

                if (
                    $invoice->getTotalPaid() == $invoice->getAmount()
                    || $invoice->getTotalPaid() == $invoiceAmount
                ) {
                    if ($invoiceCurrency !== $accountCurrency) {
                        $realAmountPaidTotal = null;

                        /* TODO: Refactor this */
                        foreach ($invoicePartPayments as $partPayment) {
                            $realAmountPaidTotal += $partPayment->getRealAmountPaid();
                        }

                        $invoice->setRealAmountPaid(strval($realAmountPaidTotal));
                    }

                    $invoice->setPaymentStatus('Paid');
                    $invoice->setInvoiceDatePaid($invoicePartPaymentTransaction->getDate());
                }
            }
        } else {
            foreach ($deletedPartPaymentsRows as $row) {
                $rowDatePaid = $row->getDatePaid();
                $rowAmount = $row->getAmount();
                $rowRealAmountPaid = $row->getRealAmountPaid();
                $rowTransaction = $row->getTransaction();

                if ($rowRealAmountPaid) {
                    $account->setBalance($rowRealAmountPaid, true);
                    $invoice->setRealAmountPaid($rowRealAmountPaid, false);
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $rowTransaction,
                        $rowDatePaid,
                        $rowRealAmountPaid,
                        true
                    );
                } else {
                    $account->setBalance($rowAmount, true);
                    $this->updateLaterTransactionsBalancesTransaction(
                        $account,
                        $rowTransaction,
                        $rowDatePaid,
                        $rowAmount,
                        true
                    );
                }

                $invoice->setTotalPaid($rowAmount, false);

                $transactionRepository->remove($rowTransaction);

                if ($rowTransaction->isBankFeeAdded()) {
                    $bankFeeAmount = $rowTransaction->getBankFeeAmount();
                    $invoice->setBankFeeAmount($bankFeeAmount, false);

                    /* Delete connected Account charge transaction */
                    foreach ($rowTransaction->getTransactions() as $transaction) {
                        if ($transaction->getTransactionTypeName() == 'Account Charge') {
                            $account->setBalance($bankFeeAmount, true);

                            $this->updateLaterTransactionsBalancesTransaction(
                                $account,
                                $transaction,
                                $transaction->getDate(),
                                $bankFeeAmount,
                                true
                            );

                            $transactionRepository->remove($transaction, true);
                        }
                    }
                }

                foreach ($invoicePartPayments as $partPayment) {
                    if ($partPayment->getCollectionOrderNumber() > $row->getCollectionOrderNumber()) {
                        $partPayment->getRestPaymentAmount($rowAmount, true);
                    }
                }
            }
        }
    }
}
