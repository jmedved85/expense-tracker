<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\Transaction;
use App\Repository\InvoiceRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Exception;
use TypeError;

class ErrorHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws Exception
     */
    public function handleDatabaseErrors($ex, Transaction $transaction = null): void
    {
        if ($ex instanceof TypeError) {
            $this->logger->error('Type error occurred during database operation: ' . $ex->getMessage());
        } elseif ($ex instanceof Exception) {
            $this->logger->error('Exception occurred during database operation: ' . $ex->getMessage());
        } else {
            $this->logger->error('An unexpected error occurred during database operation: ' . $ex->getMessage());
        }

        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }

        if ($transaction) {
            /** @var TransactionRepository $transactionRepository*/
            $transactionRepository = $this->entityManager->getRepository(Transaction::class);
            /** @var InvoiceRepository $invoiceRepository */
            $invoiceRepository = $this->entityManager->getRepository(Invoice::class);

            $account = $transaction->getMainAccount();

            if ($transaction->getInvoice()) {
                $invoice = $transaction->getInvoice();

                $this->entityManager->beginTransaction(); // Start a transaction

                $account->setBalance($transaction->getAmount(), true);

                if ($invoice->isBankFeeAdded()) {
                    $account->setBalance($invoice->getBankFeeAmount(), true);
                }

                $transactionRepository->remove($transaction, true);
                $invoiceRepository->remove($invoice, true);

                $this->entityManager->commit(); // Commit the transaction
            }
        }
    }
}
