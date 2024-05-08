<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Account;
use App\Entity\Budget;
use App\Entity\Department;
use App\Entity\Purchase;
use App\Entity\Supplier;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Entity\Unit;
use App\Form\Type\EmptyType;
use App\Repository\PurchaseRepository;
use App\Repository\TransactionRepository;
use App\Repository\UnitRepository;
use App\Service\BudgetCalculationService;
use App\Service\ErrorHandler;
use App\Service\TransactionService;
use App\Traits\AdminTrait;
use App\Validator\Constraints\LineNetTotalDifference;
use App\Validator\Constraints\NoPurchaseLineAdded;
use DateTime;
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
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\Form\Type\CollectionType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PurchaseAdmin extends AbstractAdmin
{
    use AdminTrait;

    private EntityManagerInterface $entityManager;
    private TokenStorageInterface $tokenStorage;
    // private ErrorHandler $errorHandler;
    // protected TransactionService $transactionService;
    // protected BudgetCalculationService $budgetCalculationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        // ErrorHandler $errorHandler,
        // TransactionService $transactionService,
        // BudgetCalculationService $budgetCalculationService
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        // parent::__construct(null, Purchase::class, null);
        // $this->errorHandler = $errorHandler;
        // $this->transactionService = $transactionService;
        // $this->budgetCalculationService = $budgetCalculationService;
    }

    // protected function configureRoutes(RouteCollectionInterface $collection): void
    // {
    //     $collection
    //         ->add('addBankFeeLinkToModal', $this->getRouterIdParameter().'/addBankFeeLinkToModal')
    //         ->add('addBankFee', $this->getRouterIdParameter().'/addBankFee')
    //     ;
    // }

    // /* Comment to add 'Add new' button from te Purchases list */
    // protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    // {
    //     $buttonList['create'] = false;

    //     return $buttonList;
    // }

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

        // if ($unitId) {
        //     $accountIds = $this->getPurchaseAccountIds(strval($unitId));
        // } else {
        //     $accountIds = $this->getPurchaseAccountIds();
        // }

        $filter
            ->add('dateOfPurchase', DateRangeFilter::class, [
                'field_type' => DateRangePickerType::class,
                'field_options' => [
                    'field_options' => [
                        'format' => 'dd.MM.yyyy'
                    ]
                ]
            ])
            ->add('currency', null, [
                'label' => 'Currency',
                'field_type' => CurrencyType::class,
                'field_options' => [
                    'preferred_choices' => $this->preferredCurrencyChoices,
                ]
            ])
            ->add('account', null, [
                'label' => 'Account',
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Account::class,
                    // 'choice_label' => (!$unitId) ? 'nameWithCurrencyUnitBalance' : 'nameWithCurrencyBalance',
                    'choice_label' => 'nameWithCurrencyBalance',
                    // 'query_builder' => function (EntityRepository $er) use ($unitId, $accountIds) {
                    //     if (!$unitId) {
                    //         return $er->createQueryBuilder('a')
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
            ->add('supplier', null, [
                'label' => 'Supplier',
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Supplier::class,
                    // 'choice_label' => !$unitId ? 'nameWithUnit' : 'name',
                    'choice_label' => 'name',
                    // 'query_builder' => function (EntityRepository $er) use ($unitId) {
                    //     if (!$unitId) {
                    //         return $er->createQueryBuilder('s')
                    //             ->join('s.unit', 'u')
                    //             ->andWhere('u.active = :active')
                    //             ->orderBy('s.name', 'ASC')
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
                    // 'choice_label' => !$unitId ? 'nameWithUnit' : 'name',
                    'choice_label' => 'name',
                    // 'query_builder' => function (EntityRepository $er) use ($unitId) {
                    //     if (!$unitId) {
                    //         return $er->createQueryBuilder('d')
                    //             ->join('d.unit', 'u')
                    //             ->andWhere('u.active = :active')
                    //             ->orderBy('d.name', 'ASC')
                    //             ->setParameter('active', true)
                    //         ;
                    //     } else {
                    //         return $er->createQueryBuilder('d')
                    //             ->where('d.unit = :unitId')
                    //             ->orderBy('d.name', 'ASC')
                    //             ->setParameter('unitId', $unitId)
                    //         ;
                    //     }
                    // },
                ],
            ])
            ->add('description', null, [
                'label' => 'Description'
            ])
        ;
    }

    // MARK: - List Fields
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('dateOfPurchase', 'date', [
                'label' => 'Purchase Date',
                'pattern' => 'd/M/Y',
            ])
            ->add('account.nameWithCurrency', null, [
                'label' => 'Account',
                'header_style' => 'width: 200px;'
            ])
            ->add('transactionTypeName', null, [
                'label' => 'Type of Payment'
            ])
            ->add('budget', null, [
                'label' => 'Budget'
            ])
            ->add('department.name', null, [
                'label' => 'Department',
            ])
            ->add('supplier.name', null, [
                'label' => 'Supplier',
                'header_style' => 'width: 130px;'
            ])
            ->add('description', null, [
                'label' => 'Description',
                'header_style' => 'width: 230px;'
            ])
            ->add('currency', null, [
                'row_align' => 'center',
                'header_style' => 'text-align: center',
            ])
            ->add('amount', MoneyType::class, [
                'template' => 'CRUD/list_amount.html.twig',
                'row_align' => 'right',
                'header_style' => 'text-align: right',
            ])
            // ->add('file', null, [
            //     'label' => 'Doc File(s)',
            //     'template' => 'CRUD/list_document_file.html.twig',
            //  'row_align' => 'center',
            //  'header_style' => 'text-align: center',
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
        $now = new DateTime();

        // /* Get unit */
        // $unitId = $this->getUnitId();

        $editRoute = $this->isCurrentRoute('edit');

        if ($editRoute) {
            /** @var Purchase $subject  */
            $subject = $this->getSubject();

            $accountId = $subject->getAccount()->getId();
            $accountName = $subject->getAccount()->getNameWithCurrency();
            $accountType = $subject->getAccount()->getAccountType();
            $accountCurrency = $subject->getCurrency();
            // $unitId = strval($subject->getAccount()->getUnit()->getid());
        } else {
            $accountData = $this->getRequest()->query->all();

            if (count($accountData) == 1) {
                $accountData = $this->session()->get('accountData');
            } else {
                $this->session()->set('accountData', $accountData);
            }

            $accountId = $accountData['accountId'] ?? null;
            $accountName = $accountData['accountName'] ?? null;
            $accountType = $accountData['accountType'] ?? null;
            $accountCurrency = $accountData['accountCurrency'] ?? null;
            $unitId = $accountData['unitId'] ?? null;
        }

        // if ($accountType == 'Cash Account') {
        //     $transactionType = 'Cash payment';
        // } else {
        //     $transactionType = 'Card payment';
        // }

        // if ($unitId) {
            $form
                ->with('accountName', [
                    // 'label' => $accountName . ' - ' . 'Purchase',
                    'label' => 'Purchase',
                    'class' => 'col-md-6'
                ])
                    /* Only available Cash payment & Card payment */
                    ->add('account', EntityType::class, [
                        'class' => Account::class,
                        'choice_label' => 'nameWithCurrencyBalance',
                        'label' => 'Account Name',
                        'query_builder' => function (EntityRepository $er) use ($accountId) {
                            return $er->createQueryBuilder('a')
                                ->where('a.id = :account_id')
                                ->setParameter('account_id', $accountId);
                        },
                    ])
                    ->add('transactionType', ChoiceType::class, [
                        'placeholder' => 'Choose an option',
                        'label' => 'Type of Payment',
                        'choices' => TransactionType::NAMES,
                        ])
                        // ->add('transactionType', EntityType::class, [
                        //     'class' => TransactionType::class,
                        //     'choice_label' => 'name',
                        //     'label' => 'Type of Payment',
                        //     'query_builder' => function (EntityRepository $er) use ($transactionType) {
                        //         return $er->createQueryBuilder('tt')
                        //             ->where('tt.name = :transactionType')
                        //             ->setParameter('transactionType', $transactionType);
                        //     },
                        // ])
                    ->add('dateOfPurchase', DatePickerType::class, [
                        'years' => range(1900, $now->format('Y')),
                        'required' => true,
                        'format' => 'dd.MM.yyyy',
                        'label' => 'Date of Purchase',
                    ])
                    ->add('currency', TextType::class, [
                        'data' => $accountCurrency,
                        'disabled' => true,
                    ])
                    ->add('budget', EntityType::class, [
                        'class' => Budget::class,
                        'required' => false,
                        'choice_label' => function (Budget $budget) {
                            return $budget->getName();
                        },
                        // 'query_builder' => function (EntityRepository $er) use ($unitId) {
                        //     return $er->createQueryBuilder('b')
                        //         ->where('b.unit = :unit')
                        //         ->addOrderBy('b.name', 'ASC')
                        //         ->setParameter('unit', $unitId)
                        //     ;
                        // },
                    ])
                    ->add('department', EntityType::class, [
                        'class' => Department::class,
                        'placeholder' => 'Choose an option',
                        'choice_label' => 'name',
                        'required' => false,
                        // 'query_builder' => function (EntityRepository $er) use ($unitId) {
                        //     return $er->createQueryBuilder('d')
                        //         ->andWhere('d.unit = :unitId')
                        //         ->orderBy('d.name', 'ASC')
                        //         ->setParameter('unitId', $unitId)
                        //     ;
                        // },
                    ])
                    ->add('description', TextareaType::class, [
                        'required' => false,
                        'label' => 'Description',
                    ])
                    ->add('supplier', ModelListType::class, [
                        'class' => Supplier::class,
                        'btn_delete' => false,
                        'btn_list' => 'Select',
                    ])
                    // ->add('supplier', EntityType::class, [
                    //     'class' => Supplier::class,
                    //     'placeholder' => 'Choose an option',
                    //     'choice_label' => 'name',
                    //     'required' => false,
                    //     'query_builder' => function (EntityRepository $er) use ($unitId) {
                    //         return $er->createQueryBuilder('s')
                    //             ->andWhere('s.unit = :unitId')
                    //             ->orderBy('s.name', 'ASC')
                    //             ->setParameter('unitId', $unitId)
                    //         ;
                    //     },
                    //     'attr' => [
                    //         'style' => 'width: 75%;'
                    //     ],
                    // ])
                ->end()
                ->with('Payments', [
                    'class' => 'col-md-6'
                ])
                    ->add('amount', MoneyType::class, [
                        'label' => 'Amount (Purchase Lines Total)',
                        'grouping' => true, // NOTE: Currency digit grouping; it is needed for validation to pass
                        'currency' => $editRoute ? $accountCurrency : null,
                        'disabled' => true,
                        'required' => false,
                    ])
                ;
        if ($accountType == 'Card Account') {
            if (($key = array_search($accountCurrency, $this->preferredCurrencyChoices)) !== false) {
                unset($this->preferredCurrencyChoices[$key]);
            }

            $form
                ->add('realCurrency', CurrencyType::class, [
                    'label' => 'Real Currency',
                    'placeholder' => 'Choose an option',
                    'preferred_choices' => $this->preferredCurrencyChoices,
                    'required' => false,
                ])
                ->add('realAmountPaid', MoneyType::class, [
                    'label' => 'Real Amount Paid',
                    'grouping' => true, // NOTE: Currency digit grouping; it is needed for validation to pass
                    'required' => false,
                    'attr' => [
                        'style' => 'text-align: end;'
                    ]
                ])
            ;
        }
            $form
                ->end()
                ->with('File Uploads', [
                    'class' => 'col-sm-12 sepBefore'
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
                ->with('Purchase Lines', ['class' => 'col-sm-12 inlineFormFieldsets3 sepBefore'])
                    ->add('purchaseLines', CollectionType::class, [
                        'label' => false,
                        'constraints' => [
                            new NoPurchaseLineAdded(),
                            new LineNetTotalDifference(),
                        ],
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable'  => 'position',
                    ])
                ->end()
            ;
        // } else {
        //     /* TODO: make better solution, like custom type which will have custom template */
        //     $form
        //         ->add('emptyType', EmptyType::class, [
        //             'label' => 'Please select an Unit to create and edit Purchases',
        //             'mapped' => false,
        //             'required' => false
        //         ])
        //     ;
        // }
    }

    // MARK: - Show Fields
    protected function configureShowFields(ShowMapper $show): void
    {
        /** @var Purchase $subject  */
        $subject = $this->getSubject();

        // /* Get unit */
        // $unitId = $this->getUnitId();
        $currency = $subject->getCurrency();
        $realAmountCurrency = $subject->getRealCurrencyPaid();
        $accountName = $subject->getAccount() ? $subject->getAccount()->getNameWithCurrency() : null;

        $show
            ->with('accountName', [
                'label' => $accountName . ' - ' . 'Purchase',
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
            ->add('account', null, [
                'label' => 'Account',
            ])
            ->add('dateOfPurchase', 'date', [
                'label' => 'Purchase Date',
                'format' => 'd/m/Y'
            ])
            ->add('transactionTypeName', null, [
                'label' => 'Type of Payment'
            ])
            ->add('budget.name', null, [
                'label' => 'Budget'
            ])
            ->add('department.name', null, [
                'label' => 'Department'
            ])
            ->add('supplier.name', null, [
                'label' => 'Supplier'
            ])
            ->add('description', null, [
                'label' => 'Description'
            ])
            ->add('currency')
            ->add('amount', MoneyType::class, [
                'label' => 'Total Amount',
                'template' => 'CRUD/show_currency.html.twig',
                'currency' => $currency
            ])
        ;

        if ($subject->getTransactionType()::CREDIT_CARD_PAYMENT) {
            $show
                ->add('realAmountPaid', MoneyType::class, [
                    'label' => 'Real Amount Paid',
                    'template' => 'CRUD/show_currency.html.twig',
                    'currency' => $realAmountCurrency
                ])
            ;
        }

        $show
            ->add('addedByUserDateTime', null, [
                'label' => 'Added By'
            ])
            ->add('editedByUserDateTime', null, [
                'label' => 'Last Edit By'
            ])
        ->end()
        ->with('Second column', [
            'label' => 'File Preview',
            'class' => 'col-md-6'
        ])
            // ->add('file', null, [
            //     'template' => 'CRUD/show_one_to_many_document_file.html.twig'
            // ])
        ->end()
        ->with('Purchase Lines')
            ->add('purchaseLines', CollectionType::class, [
                'template' => 'Purchase/purchase_line_show_field.html.twig',
                'currency' => $currency,
            ])
        ->end()
        ;
    }

    // MARK: - PrePersist
    /**
     * @throws Exception
     */
    protected function prePersist(object $object): void
    {
        /** @var Purchase $object  */

        /** @var UnitRepository $unitRepository */
        $unitRepository = $this->entityManager->getRepository(Unit::class);

        /* Get data for create */
        $data = $this->getDataForCreateAndUpdate($object);

        // /* Get unit */
        // $unitId = $this->getUnitId();

        $userAndDateTime = $this->getUserAndDateTime();

        $user = $data['user'];
        $account = $data['account'];
        $dateTime = $data['dateTime'];
        $currency = $data['currency'];
        $amountTotal = $data['amountTotal'];

        // if ($unitId) {
        //     $unit = $unitRepository->findOneBy([
        //         'id' => $unitId
        //     ]);
        // } else {
        //     $unit = $account->getUnit();
        // }

        /* Persist created data in Purchase table */
        // $object->setUnit($unit);
        $object->setDateTimeAdded($dateTime);
        $object->setCurrency($currency);
        $object->setAmount($amountTotal);
        $object->setAddedByUser($user);
        $account->setBalance($amountTotal, false);

        // $lastTransactionBalance = $this->getLastTransactionBalance($object->getDateOfPurchase(), $account);
        // $balance = $lastTransactionBalance - $amountTotal;

        // /* New record in transaction table */
        // $purchaseTransaction = $this->transactionService->createOrUpdatePurchaseTransaction(
        //     $object,
        //     $object->getDateOfPurchase(),
        //     $unit,
        //     $object->getTransactionType(),
        //     $account,
        //     $currency,
        //     $amountTotal,
        //     $userAndDateTime,
        //     $balance,
        //     'create'
        // );

        // $this->updateLaterTransactionsBalancesTransaction(
        //     $account, $purchaseTransaction, $object->getDateOfPurchase(), $amountTotal, false);

        $session = $this->session();

        if ($session->get('accountData')) {
            $session->remove('accountData');
        }
    }

    // MARK: - PreUpdate
    /**
     * @throws Exception
     */
    protected function preUpdate(object $object): void
    {
        /** @var Purchase $object  */

        $userAndDateTime = $this->getUserAndDateTime();

        $flag = 'update';

        /* Get data for update */
        $data = $this->getDataForCreateAndUpdate($object);

        $user = $data['user'];
        $account = $data['account'];
        $dateTime = $data['dateTime'];
        $amountTotal = $data['amountTotal'];
        $amountTotalDifference = $data['amountTotalDifference'];
        $currentAmount = $data['currentAmount'];
        $currentDate = $data['currentDate'];

        /* Persist updated data */
        $object->setEditedByUser($user);
        $object->setEditedByUserDeleted(null);
        $object->setDateTimeEdited($dateTime);
        $object->setAmount($amountTotal);
        $account->setBalance($amountTotalDifference, false);

        /* Persist updated data in Transactions table */
        // $transaction = $object->getTransaction();

        // if ($object->getDateOfPurchase()->format('Y-m-d') !== $currentDate->format('Y-m-d')) {
        //     if ($amountTotal != $currentAmount) {
                // $this->updateLaterTransactionsBalances(
                //     $account, $transaction, $transaction->getDate(), $currentAmount, true);
        //     } else {
                // $this->updateLaterTransactionsBalances(
                //     $account, $transaction, $transaction->getDate(), $amountTotal, true);
        //     }

        //     $lastTransactionBalance = $this->getLastTransactionBalance(
        //         $object->getDateOfPurchase(), $account, $transaction->getTransactionNumber());
        //     $balance = $lastTransactionBalance - $amountTotal;

        //     /* Update record in transaction table */
        //     $purchaseTransaction = $this->transactionService->createOrUpdatePurchaseTransaction(
        //         $object,
        //         $object->getDateOfPurchase(),
        //         $object->getUnit(),
        //         $object->getTransactionType(),
        //         $account,
        //         $object->getCurrency(),
        //         $amountTotal,
        //         $userAndDateTime,
        //         $balance,
        //         $flag,
        //         $transaction
        //     );

        //     $this->updateLaterTransactionsBalances(
        //         $account, $purchaseTransaction, $purchaseTransaction->getDate(), $amountTotal, false);
        // } else {
        //     $balance = $transaction->recalculateBalance($amountTotalDifference, false);

        //     /* Update record in transaction table */
        //     $purchaseTransaction = $this->transactionService->createOrUpdatePurchaseTransaction(
        //         $object,
        //         $object->getDateOfPurchase(),
        //         $object->getUnit(),
        //         $object->getTransactionType(),
        //         $account,
        //         $object->getCurrency(),
        //         $amountTotal,
        //         $userAndDateTime,
        //         floatval($balance),
        //         $flag,
        //         $transaction
        //     );

        //     $this->updateLaterTransactionsBalances(
        //         $account, $purchaseTransaction, $purchaseTransaction->getDate(), $amountTotalDifference, false);
        // }
    }

    // MARK: - PreRemove
    protected function preRemove(object $object): void
    {
        /** @var Purchase $object  */

        // /** @var TransactionRepository $transactionRepository */
        // $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        // $flag = 'remove';

        // $account = $object->getAccount();

        // if ($object->isBankFeeAdded()) {
        //     /* Return the amount + bank fee to the account */
        //     $amount = $object->getAmount();
        //     $bankFeeAmount = $object->getBankFeeAmount();
        //     $account->setBalance(strval(floatval($amount) + floatval($bankFeeAmount)), true);
        // } else {
        //     /* Return the amount to the account */
        //     $amount = $object->getAmount();
        //     $account->setBalance($amount, true);
        // }

        // $transaction = $object->getTransaction();

        // $this->updateLaterTransactionsBalances($account, $transaction, $transaction->getDate(), $amount, true);

        // /* BUDGET ACTUAL EXPENSES */
        // if ($object->getBudget()) {
        //     $this->budgetCalculationService->calculateBudgetsActualExpenses($object, null, $flag);
        // }

        // $transactionRepository->remove($transaction, true);
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
        //     $accountIds = $this->getAccountIds($unitId);

        //     $qb
        //         ->andWhere($rootAlias . '.account IN (:accountIds)')
        //         ->addOrderBy($rootAlias . '.dateTimeAdded', 'DESC')
        //         ->setParameter('accountIds', $accountIds)
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
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::SORT_BY] = 'dateOfPurchase';
    }

    private function getDataForCreateAndUpdate(Purchase $purchase): array
    {
        /** @var PurchaseRepository $purchaseRepository */
        $purchaseRepository = $this->entityManager->getRepository(Purchase::class);

        /* Get logged in user from the token */
        $user = $this->tokenStorage->getToken()->getUser();

        /* Get actual dateTime */
        $dateTime = new DateTime();

        /* Get currency */
        $currency = $purchase->getCurrency();

        /* Get lineTotals and calculate into the $amountTotal */
        $purchaseLines = $purchase->getPurchaseLines();
        $amountTotal = 0;

        foreach ($purchaseLines as $purchaseLine) {
            $lineTotal = $purchaseLine->getLineTotal();
            $amountTotal += $lineTotal;
        }

        $currentAmount = $purchase->getAmount();

        if ($purchase->getId() !== null) {
            $currentDate = $purchaseRepository->getOldDate($purchase->getId());
        } else {
            $currentDate = $purchase->getDateOfPurchase();
        }

        $amountTotalStr = strval($amountTotal);
        $amountTotalDifference = strval($amountTotal - $currentAmount);

        $data['user'] = $user;
        $data['account'] = $purchase->getAccount();
        $data['dateTime'] = $dateTime;
        $data['currency'] = $currency ?: $purchase->getAccount()->getCurrency();
        $data['amountTotal'] = $amountTotalStr;
        $data['amountTotalDifference'] = $amountTotalDifference;
        $data['currentAmount'] = $currentAmount;
        $data['currentDate'] = $currentDate;

        return $data;
    }
}
