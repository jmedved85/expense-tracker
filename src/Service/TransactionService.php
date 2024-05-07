<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Invoice;
use App\Entity\Purchase;
use App\Entity\Unit;
use App\Entity\TransactionType;
use App\Repository\TransactionRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Transaction;
use Exception;
use TypeError;

class TransactionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ErrorHandler $errorHandler,
        private BudgetCalculationService $budgetCalculationService
    ) {
    }

    /**
     * @throws Exception
     */
    public function createOrUpdateInvoiceTransaction(
        Invoice $object,
        DateTimeInterface $datePaid,
        Unit $unit,
        TransactionType $bankPaymentTransactionType,
        Account $account,
        string $currency,
        string $invoiceCurrency,
        string $amountTotal,
        array $userAndDateTime,
        float $balance,
        string $flag,
        string $realAmountPaid = null,
        Transaction $transaction = null
    ): Transaction {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        try {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $originalData = $unitOfWork->getOriginalEntityData($object);

            $this->entityManager->beginTransaction(); // Start a transaction

            if ($flag == 'create') {
                $transaction = new Transaction();
                $transaction->setTransactionNumber($this->getAutoTransactionNumber($unit));
            }
            $transaction->setDate($datePaid);
            $transaction->setUnit($unit);
            $transaction->setBudget($object->getBudget());
            $transaction->setTransactionType($bankPaymentTransactionType);
            $transaction->setMainAccount($account);
            $transaction->setSupplier($object->getSupplier());
            $transaction->setDepartment($object->getDepartment());
            if (!empty($object->getInvoicePartPayments()->toArray())) {
                $transaction->setShortDescription($object->getShortDescription() . ' - Part-Payment');
            } else {
                $transaction->setShortDescription($object->getShortDescription());
            }
            if (($currency !== $invoiceCurrency) && $realAmountPaid) {
                $transaction->setAmount($realAmountPaid);
                $transaction->setMoneyOut($realAmountPaid);
                $transaction->setCurrency($currency);
            } else {
                $transaction->setAmount($amountTotal);
                $transaction->setMoneyOut($amountTotal);
                $transaction->setCurrency($invoiceCurrency);
            }
            if ($flag == 'create') {
                $transaction->setAddedByUser($userAndDateTime['user']);
                $transaction->setDateTimeAdded($userAndDateTime['dateTime']);
            } else {
                $transaction->setDateTimeEdited($userAndDateTime['dateTime']);
            }
            $transaction->setBalanceMainAccount(strval($balance));
            $transaction->setInvoice($object);

            /* BUDGET ACTUAL EXPENSES */
            if ($object->getBudget()) {
                // If there is a change in Budget (month / year) - from non to yes
                if ($this->budgetCalculationService->isValidOriginalData($originalData, 'budget_id')) {
                    if ($originalData['budget_id'] == null) {
                        $flag = 'create';
                    }
                }

                $this->budgetCalculationService->calculateBudgetsActualExpenses($object, null, $flag);
            } else {
                // If there is a change in Budget (month / year) - from yes to none
                if ($this->budgetCalculationService->isValidOriginalData($originalData, 'budget_id')) {
                    $this->budgetCalculationService->calculateBudgetsActualExpenses(
                        $object,
                        $originalData['budget_id'],
                        'remove'
                    );
                }
            }

            $transactionRepository->add($transaction, true);

            $this->entityManager->commit(); // Commit the transaction

            return $transaction;
        } catch (TypeError | Exception $ex) {
            $this->errorHandler->handleDatabaseErrors($ex);

            throw $ex;
        }
    }

    /**
     * @throws Exception
     */
    public function createOrUpdatePurchaseTransaction(
        Purchase $object,
        DateTimeInterface $dateOfPurchase,
        Unit $unit,
        TransactionType $transactionType,
        Account $account,
        string $currency,
        string $amountTotal,
        array $userAndDateTime,
        float $balance,
        string $flag,
        Transaction $transaction = null
    ): Transaction {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        try {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $originalData = $unitOfWork->getOriginalEntityData($object);

            $this->entityManager->beginTransaction(); // Start a transaction

            if ($flag == 'create') {
                $transaction = new Transaction();
                $transaction->setTransactionNumber($this->getAutoTransactionNumber($unit));
            }
            $transaction->setDate($dateOfPurchase);
            $transaction->setUnit($unit);
            $transaction->setBudget($object->getBudget());
            $transaction->setTransactionType($transactionType);
            $transaction->setMainAccount($account);
            $transaction->setSupplier($object->getSupplier());
            $transaction->setDepartment($object->getDepartment());
            $transaction->setShortDescription($object->getDescription());
            $transaction->setAmount($amountTotal);
            $transaction->setMoneyOut($amountTotal);
            $transaction->setCurrency($currency);
            if ($object->getRealAmountPaid()) {
                $transaction->setRealAmountPaid($object->getRealAmountPaid());
                $transaction->setRealCurrency($object->getRealCurrencyPaid());
            }
            if ($flag == 'create') {
                $transaction->setAddedByUser($userAndDateTime['user']);
                $transaction->setDateTimeAdded($userAndDateTime['dateTime']);
            } else {
                $transaction->setDateTimeEdited($userAndDateTime['dateTime']);
            }
            $transaction->setBalanceMainAccount(strval($balance));
            $transaction->setPurchase($object);

            /* BUDGET ACTUAL EXPENSES */
            if ($object->getBudget()) {
                // If there is a change in Budget (month / year) - from non to yes
                if ($this->budgetCalculationService->isValidOriginalData($originalData, 'budget_id')) {
                    if ($originalData['budget_id'] == null) {
                        $flag = 'create';
                    }
                }

                $this->budgetCalculationService->calculateBudgetsActualExpenses($object, null, $flag);
            } else {
                // If there is a change in Budget (month / year) - from yes to none
                if ($this->budgetCalculationService->isValidOriginalData($originalData, 'budget_id')) {
                    $this->budgetCalculationService->calculateBudgetsActualExpenses(
                        $object,
                        $originalData['budget_id'],
                        'remove'
                    );
                }
            }

            $transactionRepository->add($transaction, true);

            $this->entityManager->commit(); // Commit the transaction

            return $transaction;
        } catch (TypeError | Exception $ex) {
            $this->errorHandler->handleDatabaseErrors($ex);

            throw $ex;
        }
    }

    /**
     * @throws Exception
     */
    public function createOrUpdateBankFeeTransaction(
        DateTimeInterface $date,
        Unit $unit,
        TransactionType $bankFeeTransactionType,
        Account $account,
        Transaction $transaction,
        string $amount,
        array $userAndDateTime,
        float $balance,
        string $flag,
        Transaction $bankFeeTransaction = null
    ): Transaction {
        /** @var TransactionRepository $transactionRepository*/
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        try {
            $this->entityManager->beginTransaction(); // Start a transaction

            if ($flag == 'create') {
                $bankFeeTransaction = new Transaction();
                $bankFeeTransaction->setTransactionNumber($this->getAutoTransactionNumber($unit));
            }
            $bankFeeTransaction->setDate($date);
            $bankFeeTransaction->setUnit($unit);
            $bankFeeTransaction->setTransactionType($bankFeeTransactionType);
            $bankFeeTransaction->setMainAccount($account);
            if ($transaction->getInvoice()) {
                $bankFeeTransaction->setShortDescription(
                    'Bank Fee for Invoice nr. "' .
                    $transaction->getInvoice()->getInvoiceNumber() .
                    '" (Transaction nr. ' . $transaction->getTransactionNumber() . ')'
                );
                $bankFeeTransaction->setInvoice($transaction->getInvoice());
            } else {
                $bankFeeTransaction->setShortDescription(
                    'Bank Fee for Transaction No. ' .
                    $transaction->getTransactionNumberString() . ' on ' .
                    $transaction->getDate()->format('d/m/Y')
                );
            }
            $bankFeeTransaction->setTransaction($transaction);
            $bankFeeTransaction->setAmount($amount);
            $bankFeeTransaction->setMoneyOut($amount);
            $bankFeeTransaction->setCurrency($account->getCurrency());
            if ($flag == 'create') {
                $bankFeeTransaction->setAddedByUser($userAndDateTime['user']);
                $bankFeeTransaction->setDateTimeAdded($userAndDateTime['dateTime']);
            } else {
                $bankFeeTransaction->setDateTimeEdited($userAndDateTime['dateTime']);
            }
            $bankFeeTransaction->setBalanceMainAccount(strval($balance));
            $transactionRepository->add($bankFeeTransaction, true);

            $this->entityManager->commit(); // Commit the transaction

            return $bankFeeTransaction;
        } catch (TypeError | Exception $ex) {
            $this->errorHandler->handleDatabaseErrors($ex, $transaction);

            throw $ex;
        }
    }

    /**
     * @throws Exception
     */
    public function createAddFundsTransaction(
        DateTimeInterface $date,
        Unit $unit,
        TransactionType $transactionType,
        ?Account $accountFrom = null,
        Account $accountTo,
        string $funds,
        string $realAmount,
        string $description,
        array $userAndDateTime,
        ?float $balanceTransferFrom = null,
        float $balanceTransferTo
    ): Transaction {
        /** @var TransactionRepository $transactionRepository*/
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        $accountFromCurrency = $accountFrom ? $accountFrom->getCurrency() : null;
        $accountToCurrency = $accountTo->getCurrency();

        try {
            $this->entityManager->beginTransaction(); // Start a transaction

            $transaction = new Transaction();
            $transaction->setDate($date);
            $transaction->setTransactionNumber($this->getAutoTransactionNumber($unit));
            $transaction->setUnit($unit);
            $transaction->setTransactionType($transactionType);
            $transaction->setShortDescription($description);
            /* NOTE: Auto-generated description */
            // $transaction->setShortDescription('Bank transfer to ' . $accountTo->getName());
            $transaction->setMainAccount($accountTo);
            $transaction->setBalanceMainAccount(strval($balanceTransferTo));
            $transaction->setTransferFromAccount($accountFrom);
            $transaction->setTransferToAccount($accountTo);
            if ($balanceTransferFrom) {
                $transaction->setBalanceTransferFromAccount(strval($balanceTransferFrom));
            }
            $transaction->setBalanceTransferToAccount(strval($balanceTransferTo));
            $transaction->setAmount($funds);
            $transaction->setMoneyIn($funds);
            if (!$accountFrom && $transactionType::CASH_TRANSFER) {
                $transaction->setBankFeeNotAdded(true);
            }
            if (($accountToCurrency !== $accountFromCurrency) && $realAmount) {
                $transaction->setRealAmountPaid($realAmount);
                $transaction->setRealCurrency($accountFromCurrency);
            }
            $transaction->setCurrency($accountToCurrency);
            $transaction->setAddedByUser($userAndDateTime['user']);
            $transaction->setDateTimeAdded($userAndDateTime['dateTime']);
            $transactionRepository->add($transaction, true);

            $this->entityManager->commit(); // Commit the transaction

            return $transaction;
        } catch (TypeError | Exception $ex) {
            $this->errorHandler->handleDatabaseErrors($ex);

            throw $ex;
        }
    }

    private function getAutoTransactionNumber(object $unit): ?int
    {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        $transactionNumber = 0;

        $lastTransaction = $transactionRepository->findOneBy(['unit' => $unit], ['transactionNumber' => 'DESC']);

        if ($lastTransaction) {
            $lastTransactionNumber = $lastTransaction->getTransactionNumber();
            $transactionNumber = ++$lastTransactionNumber;
        } else {
            $transactionNumber++;
        }

        return $transactionNumber;
    }
}
