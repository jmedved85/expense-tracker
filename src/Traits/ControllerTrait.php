<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

trait ControllerTrait
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    public function getUserAndDateTime(): array
    {
        /* Get logged in user from the token */
        $user = $this->tokenStorage->getToken()->getUser();

        /* Get actual dateTime */
        $dateTime = new DateTime();

        $data = [];

        $data['user'] = $user;
        $data['dateTime'] = $dateTime;

        return $data;
    }

    public function getLastTransactionBalance(
        DateTimeInterface $date,
        Account $account,
        int $transactionNumber = null
    ): ?string {
        $allTransactions = $this->getAllAccountTransactionsDesc($account);

        $result = [];
        $result2 = [];
        $result3 = [];

        if ($transactionNumber) {
            foreach ($allTransactions as $transaction) {
                if ($transaction->getDate()->format('Y-m-d') <= $date->format('Y-m-d')) {
                    if ($transaction->getTransactionNumber() !== $transactionNumber) {
                        $result[] = $transaction;
                    }
                }
            }
            foreach ($result as $transaction) {
                if ($transaction->getDate()->format('Y-m-d') == $date->format('Y-m-d')) {
                    if ($transaction->getTransactionNumber() < $transactionNumber) {
                        $result2[] = $transaction;
                    }
                } else {
                    $result3[] = $transaction;
                }
            }
        } else {
            foreach ($allTransactions as $transaction) {
                if ($transaction->getDate()->format('Y-m-d') <= $date->format('Y-m-d')) {
                    $result[] = $transaction;
                }
            }
        }

        if (empty($result)) {
            return null;
        } else {
            if (empty($result2) && $result3) {
                $lastTransaction = $result3[0];
            } elseif ($result2) {
                $lastTransaction = $result2[0];
            } else {
                $lastTransaction = $result[0];
            }

            $balance = '';

            if ($lastTransaction->getTransactionTypeName() == 'Currency Exchange') {
                if ($account == $lastTransaction->getTransferFromAccount()) {
                    $balance = $lastTransaction->getBalanceTransferFromAccount();
                } elseif ($account == $lastTransaction->getTransferToAccount()) {
                    $balance = $lastTransaction->getBalanceTransferToAccount();
                }
            } else {
                if ($account == $lastTransaction->getMainAccount()) {
                    $balance = $lastTransaction->getBalanceMainAccount();
                } elseif ($account == $lastTransaction->getTransferFromAccount()) {
                    $balance = $lastTransaction->getBalanceTransferFromAccount();
                } elseif ($account == $lastTransaction->getTransferToAccount()) {
                    $balance = $lastTransaction->getBalanceTransferToAccount();
                }
            }

            return $balance;
        }
    }

    public function getAllAccountTransactionsDesc(Account $account): ?array
    {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        $qb = $transactionRepository->createQueryBuilder('t');

        $query = $qb
            ->where('t.mainAccount = :account')
            ->orWhere('t.transferFromAccount = :account')
            ->orWhere('t.transferToAccount = :account')
            ->orderBy('t.date', 'DESC')
            ->addOrderBy('t.transactionNumber', 'DESC')
            ->setParameter('account', $account->getId())
        ;

        return $query->getQuery()->getResult();
    }

    /* TODO: refactor this */
    public function updateLaterTransactionsBalances(
        Transaction $transaction,
        DateTimeInterface $date,
        string $amount,
        bool $increase
    ): void {
        $this->entityManager = $this->entityManager;

        $account = $transaction->getMainAccount();

        $invoiceTransaction = $transaction->getInvoice();

        $transactions = $this->getTransactionsForUpdateBalance($account, $date);

        foreach ($transactions as $transaction) {
            if (
                $transaction->getTransactionTypeName() == 'Funds Transfer'
                || ($transaction->getTransactionTypeName() == 'Cash Transfer'
                && $transaction->getTransferFromAccount() == null)
            ) {
                $transaction->setBalanceMainAccount($amount, $increase);
                $transaction->setBalanceTransferToAccount($amount, $increase);
                $this->entityManager->persist($transaction);
                $this->entityManager->flush();
            } elseif ($transaction->getTransactionTypeName() == 'Bank Transfer') {
                if ($account == $transaction->getTransferFromAccount() && !$invoiceTransaction) {
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                    $transaction->setBalanceMainAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                } elseif ($account == $transaction->getTransferToAccount()) {
                    $transaction->setBalanceMainAccount($amount, $increase);
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                } else {
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                }
            } elseif ($transaction->getTransactionTypeName() == 'Currency Exchange') {
                if ($account == $transaction->getTransferFromAccount()) {
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                } elseif ($account == $transaction->getTransferToAccount()) {
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                }
            } elseif ($transaction->getTransactionTypeName() == 'Cash Withdrawal') {
                if ($account == $transaction->getTransferFromAccount()) {
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                } elseif ($account == $transaction->getTransferToAccount()) {
                    $transaction->setBalanceMainAccount($amount, $increase);
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                }
            } else {
                $transaction->setBalanceMainAccount($amount, $increase);
                $this->entityManager->persist($transaction);
                $this->entityManager->flush();
            }
        }
    }

    public function updateLaterTransactionsBalancesTransferFromAccount(
        Transaction $transaction,
        DateTime $date,
        string $amount,
        bool $increase
    ): void {
        $accountFrom = $transaction->getTransferFromAccount();

        $transactions = $this->getTransactionsForUpdateBalance($accountFrom, $date);

        foreach ($transactions as $transaction) {
            if ($transaction->getTransactionTypeName() == 'Funds Transfer') {
                if ($accountFrom == $transaction->getTransferFromAccount()) {
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                } elseif ($accountFrom == $transaction->getTransferToAccount()) {
                    $transaction->setBalanceMainAccount($amount, $increase);
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                }
            } elseif ($transaction->getTransactionTypeName() == 'Bank Transfer') {
                if ($accountFrom == $transaction->getTransferFromAccount()) {
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                } elseif ($accountFrom == $transaction->getTransferToAccount()) {
                    $transaction->setBalanceMainAccount($amount, $increase);
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                } else {
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                }
            } elseif ($transaction->getTransactionTypeName() == 'Currency Exchange') {
                if ($accountFrom == $transaction->getTransferFromAccount()) {
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                } elseif ($accountFrom == $transaction->getTransferToAccount()) {
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                }
            } elseif ($transaction->getTransactionTypeName() == 'Cash Withdrawal') {
                if ($accountFrom == $transaction->getTransferFromAccount()) {
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                } elseif ($accountFrom == $transaction->getTransferToAccount()) {
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                }
            } else {
                $transaction->setBalanceMainAccount($amount, $increase);
                $this->entityManager->persist($transaction);
                $this->entityManager->flush();
            }
        }
    }

    public function getTransactionsForUpdateBalance(Account $account, DateTimeInterface $date): ?array
    {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        $qb = $transactionRepository->createQueryBuilder('t');

        $query = $qb
            ->where('t.mainAccount = :account')
            ->orWhere('t.transferFromAccount = :account')
            ->orWhere('t.transferToAccount = :account')
            ->orderBy('t.date', 'ASC')
            ->addOrderBy('t.transactionNumber', 'ASC')
            ->setParameter('account', $account->getId())
        ;

        $allTransactions = $query->getQuery()->getResult();
        $transactions = [];

        foreach ($allTransactions as $transaction) {
            if ($transaction->getDate() > $date) {
                $transactions[] = $transaction;
            }
        }

        return $transactions;
    }
}
