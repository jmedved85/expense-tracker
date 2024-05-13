<?php

declare(strict_types=1);

namespace App\Controller;

use App\Admin\TransactionAdmin;
use App\Entity\Account;
use App\Entity\Invoice;
use App\Entity\InvoicePartPayment;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Repository\AccountRepository;
use App\Repository\InvoiceRepository;
use App\Repository\InvoicePartPaymentRepository;
use App\Repository\TransactionRepository;
use App\Service\ErrorHandler;
use App\Service\TransactionService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use TypeError;

class TransactionAdminController extends CRUDController
{
    protected SessionInterface $session;

    public function __construct(
        private TransactionService $transactionService,
        private ErrorHandler $errorHandler,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @var TransactionAdmin
     */
    protected $admin;

    /**
     * @param string $id
     *
     * @return Response
     */
    public function addBankFeeLinkToModalAction(string $id): Response
    {
        $object = $this->admin->getSubject();

        $template = 'Transaction/add_bank_fee_modal_form.html.twig';

        return $this->render($template, [
            'object' => $object,
        ]);
    }

    /**
     * @param string $id
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function addBankFeeAction(string $id): RedirectResponse
    {
        $request = $this->admin->getRequest();
        /** @var Session $session */
        $session = $request->getSession();

        /** @var AccountRepository $accountRepository*/
        $accountRepository = $this->entityManager->getRepository(Account::class);
        /** @var InvoiceRepository $invoiceRepository*/
        $invoiceRepository = $this->entityManager->getRepository(Invoice::class);
        /** @var InvoicePartPaymentRepository $invoicePartPaymentRepository*/
        $invoicePartPaymentRepository = $this->entityManager->getRepository(InvoicePartPayment::class);
        /** @var TransactionRepository $transactionRepository*/
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        $transaction = $this->admin->getSubject();

        $accountSelectedId = $request->request->get('accountSelect');

        if ($transaction->getInvoice()) {
            $accountSelected = $transaction->getMainAccount();
        } else {
            $accountSelected = $accountRepository->findOneBy(['id' => $accountSelectedId]);
        }

        $funds = $request->request->get('addBankFeeInput');
        $dateInput = $request->request->get('addBankFeeDate');
        $bankFeeNotAddedCheck = $request->request->get('bankFeeNotAddedCheck');
        $userAndDateTime = $this->admin->getUserAndDateTime();
        $bankFeeTransactionType = TransactionType::ACCOUNT_CHARGE;

        if (strpos($funds, ',')) {
            $funds = str_replace(',', '', $funds);
        }

        if ($funds && $dateInput && $accountSelected && !$bankFeeNotAddedCheck) {
            $date = DateTime::createFromFormat('Y-m-d', $dateInput);

            if ($transaction->getInvoice()) {
                $invoice = $transaction->getInvoice();

                try {
                    $this->entityManager->beginTransaction(); // Start a transaction

                    $accountSelected->setBalance($funds, false);
                    $accountRepository->add($accountSelected, true);

                    $unit = $invoice->getUnit();

                    $userAndDateTime = $this->admin->getUserAndDateTime();

                    $lastTransactionBalance = $this->admin->getLastTransactionBalance($date, $accountSelected);
                    $balance = floatval($lastTransactionBalance) - floatval($funds);

                    $this->entityManager->commit(); // Commit the transaction
                } catch (TypeError | Exception $ex) {
                    $this->errorHandler->handleDatabaseErrors($ex);

                    throw $ex;
                }

                if ($transaction->getInvoicePartPayment() == null) {
                    /* New record in transaction table */
                    $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                        $date,
                        $unit,
                        $bankFeeTransactionType,
                        $accountSelected,
                        $transaction,
                        $funds,
                        $userAndDateTime,
                        $balance,
                        'create'
                    );

                    try {
                        $this->entityManager->beginTransaction(); // Start a transaction

                        /* Set bank fee in invoice object */
                        $invoice->setBankFeeAdded(true);
                        $invoice->setBankFeeAmount($funds);
                        $transaction->setBankFeeAdded(true);
                        $transaction->setBankFeeAmount($funds);
                        $transaction->setBankFeeCurrency($transaction->getCurrency());

                        if ($invoice->isBankFeeNotApplicable()) {
                            $invoice->setBankFeeNotAapplicable(false);
                            $transaction->setBankFeeNotAapplicable(false);
                        }

                        $invoiceRepository->add($invoice, true);

                        $this->admin->updateLaterTransactionsBalancesTransaction(
                            $accountSelected,
                            $bankFeeTransaction,
                            $date,
                            $funds,
                            false
                        );

                        $transactionRepository->add($transaction, true);

                        $this->entityManager->commit(); // Commit the transaction
                    } catch (TypeError | Exception $ex) {
                        $this->errorHandler->handleDatabaseErrors($ex);

                        throw $ex;
                    }
                } else {
                    $invoicePartPayments = $invoice->getInvoicePartPayments()->toArray();

                    /* New record in transaction table */
                    $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                        $date,
                        $unit,
                        $bankFeeTransactionType,
                        $accountSelected,
                        $transaction,
                        $funds,
                        $userAndDateTime,
                        $balance,
                        'create'
                    );

                    try {
                        $this->entityManager->beginTransaction(); // Start a transaction

                        $transaction->setBankFeeApplicable(true);
                        $transaction->setBankFeeAmount($funds);

                        $this->admin->updateLaterTransactionsBalancesTransaction(
                            $accountSelected,
                            $bankFeeTransaction,
                            $date,
                            $funds,
                            false
                        );

                        if ($transaction->isBankFeeNotApplicable()) {
                            $transaction->setBankFeeApplicable(false);
                        }

                        $transactionRepository->add($transaction, true);

                        $invoice->setBankFeeAmount($funds, true);
                        $invoiceRepository->add($invoice, true);

                        $partPayment = $transaction->getInvoicePartPayment();
                        $partPayment->setBankFeeAmount($funds);

                        if ($partPayment->isBankFeeNotApplicable()) {
                            $partPayment->isBankFeeNotApplicable(false);
                        }

                        $invoicePartPaymentRepository->add($partPayment, true);

                        if ($invoice->getTotalPaid() == $invoice->getAmount()) {
                            $allBankFeesAddedInPartPayments = true;

                            foreach ($invoicePartPayments as $partPayment) {
                                if (!$partPayment->isBankFeeAdded()) {
                                    $allBankFeesAddedInPartPayments = false;
                                }
                            }

                            if ($allBankFeesAddedInPartPayments) {
                                $invoice->setBankFeeAdded(true);
                            } else {
                                $invoice->setBankFeeAdded(false);
                            }
                        }

                        $this->entityManager->commit(); // Commit the transaction
                    } catch (TypeError | Exception $ex) {
                        $this->errorHandler->handleDatabaseErrors($ex);

                        throw $ex;
                    }
                }
            } else {
                $unit = $transaction->getUnit();

                try {
                    $this->entityManager->beginTransaction(); // Start a transaction

                    $accountSelected->setBalance($funds, false);
                    $accountRepository->add($accountSelected, true);

                    $this->entityManager->commit(); // Commit the transaction

                    $lastTransactionBalance = $this->admin->getLastTransactionBalance($date, $accountSelected);
                    $balance = floatval($lastTransactionBalance) - floatval($funds);
                } catch (TypeError | Exception $ex) {
                    $this->errorHandler->handleDatabaseErrors($ex);

                    throw $ex;
                }

                if ($transaction->getTransactionType() == 'Cash Withdrawal') {
                    /* New record in transaction table */
                    $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                        $date,
                        $unit,
                        $bankFeeTransactionType,
                        $accountSelected,
                        $transaction,
                        $funds,
                        $userAndDateTime,
                        $balance,
                        'create'
                    );

                    $this->admin->updateLaterTransactionsBalancesTransaction(
                        $accountSelected,
                        $bankFeeTransaction,
                        $date,
                        $funds,
                        false
                    );
                } elseif ($transaction->getTransactionType() == 'Funds Transfer') {
                    /* New record in transaction table */
                    $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                        $date,
                        $unit,
                        $bankFeeTransactionType,
                        $accountSelected,
                        $transaction,
                        $funds,
                        $userAndDateTime,
                        $balance,
                        'create'
                    );

                    $this->admin->updateLaterTransactionsBalancesTransaction(
                        $accountSelected,
                        $bankFeeTransaction,
                        $date,
                        $funds,
                        false
                    );
                /* Bank transfer */
                } elseif ($transaction->getTransactionType() == 'Currency Exchange') {
                    /* New record in transaction table */
                    $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                        $date,
                        $unit,
                        $bankFeeTransactionType,
                        $accountSelected,
                        $transaction,
                        $funds,
                        $userAndDateTime,
                        $balance,
                        'create'
                    );

                    $this->admin->updateLaterTransactionsBalancesTransaction(
                        $accountSelected,
                        $bankFeeTransaction,
                        $date,
                        $funds,
                        false
                    );
                /* Bank Transfer */
                } else {
                    /* New record in transaction table */
                    $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                        $date,
                        $unit,
                        $bankFeeTransactionType,
                        $accountSelected,
                        $transaction,
                        $funds,
                        $userAndDateTime,
                        $balance,
                        'create'
                    );

                    $this->admin->updateLaterTransactionsBalancesTransaction(
                        $accountSelected,
                        $bankFeeTransaction,
                        $date,
                        $funds,
                        false
                    );
                }

                try {
                    $this->entityManager->beginTransaction(); // Start a transaction

                    /* Set bank fee in transaction object */
                    $transaction->setBankFeeAdded(true);
                    $transaction->setBankFeeAmount($funds);
                    $transaction->setBankFeeCurrency($accountSelected->getCurrency());
                    $transactionRepository->add($transaction, true);

                    $this->entityManager->commit(); // Commit the transaction
                } catch (TypeError | Exception $ex) {
                    $this->errorHandler->handleDatabaseErrors($ex);

                    throw $ex;
                }
            }
        } elseif (!$bankFeeNotAddedCheck) {
            if ($funds && $dateInput == null && $accountSelectedId) {
                $session->getFlashBag()->add('error', 'Please, pick a date');
            } elseif ($funds == null && $dateInput && $accountSelectedId) {
                $session->getFlashBag()->add('error', 'Please, enter a value');
            } elseif ($funds == null && $dateInput == null && $accountSelectedId) {
                $session->getFlashBag()->add('error', 'Please, enter a value and pick a date');
            } elseif ($funds && $dateInput && $accountSelectedId == null) {
                $session->getFlashBag()->add('error', 'Please, select an account');
            } elseif ($funds && $dateInput == null && $accountSelectedId == null) {
                $session->getFlashBag()->add('error', 'Please, pick a date and select an account');
            } elseif ($funds == null && $dateInput && $accountSelectedId == null) {
                $session->getFlashBag()->add('error', 'Please, enter a value and select an account');
            } elseif ($funds == null && $dateInput == null && $accountSelectedId == null) {
                $session->getFlashBag()->add('error', 'Please, enter a value, pick a date and select an account');
            }
        } else {
            if (!$transaction->isBankFeeNotApplicable()) {
                if ($transaction->getInvoice()) {
                    $invoice = $transaction->getInvoice();

                    if ($transaction->getInvoicePartPayment()) {
                        $partPayment = $transaction->getInvoicePartPayment();

                        try {
                            $this->entityManager->beginTransaction(); // Start a transaction

                            $partPayment->isBankFeeNotApplicable(true);
                            $transaction->isBankFeeNotApplicable(true);

                            $invoicePartPaymentRepository->add($partPayment, true);
                            $transactionRepository->add($transaction, true);

                            if ($invoice->getTotalPaid() == $invoice->getAmount()) {
                                $invoicePartPayments = $invoice->getInvoicePartPayments()->toArray();

                                $allBankFeesNotAddedInPartPayments = true;

                                foreach ($invoicePartPayments as $partPayment) {
                                    if (!$partPayment->isBankFeeNotApplicable()) {
                                        $allBankFeesNotAddedInPartPayments = false;
                                    }
                                }

                                if ($allBankFeesNotAddedInPartPayments) {
                                    $invoice->isBankFeeNotApplicable(true);
                                } else {
                                    $invoice->isBankFeeNotApplicable(false);
                                }

                                $invoiceRepository->add($invoice, true);
                            }

                            $this->entityManager->commit(); // Commit the transaction
                        } catch (TypeError | Exception $ex) {
                            $this->errorHandler->handleDatabaseErrors($ex);

                            throw $ex;
                        }
                    } else {
                        if (!$invoice->isBankFeeNotApplicable()) {
                            try {
                                $this->entityManager->beginTransaction(); // Start a transaction

                                $invoice->isBankFeeNotApplicable(true);
                                $transaction->isBankFeeNotApplicable(true);

                                $invoiceRepository->add($invoice, true);
                                $transactionRepository->add($transaction, true);

                                $this->entityManager->commit(); // Commit the transaction
                            } catch (TypeError | Exception $ex) {
                                $this->errorHandler->handleDatabaseErrors($ex);

                                throw $ex;
                            }
                        }
                    }
                } else {
                    try {
                        $this->entityManager->beginTransaction(); // Start a transaction

                        $transaction->isBankFeeNotApplicable(true);

                        $transactionRepository->add($transaction, true);

                        $this->entityManager->commit(); // Commit the transaction
                    } catch (TypeError | Exception $ex) {
                        $this->errorHandler->handleDatabaseErrors($ex);

                        throw $ex;
                    }
                }
            }
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
