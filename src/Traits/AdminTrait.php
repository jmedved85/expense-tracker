<?php

declare(strict_types=1);

namespace App\Traits;

use App\Entity\Account;
use DateTime;
use DateTimeInterface;
use Exception;
use Symfony\Component\HttpFoundation\Session\Session;

trait AdminTrait
{
    protected $preferredCurrencyChoices = ['GBP', 'EUR', 'CHF', 'USD'];

    public function session()
    {
        try {
            return $this->getRequest()->getSession();
        } catch (Exception $e) {
            return new Session();
        }
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

    /* TODO: refactor to transaction service class */
    // public function getLastTransactionBalance(
    //     DateTimeInterface $date,
    //     Account $account,
    //     int $transactionNumber = null
    // ): ?string
    // {
    //     $allTransactions = $this->getAllAccountTransactionsDesc($account);

    //     $result = [];
    //     $result2 = [];
    //     $result3 = [];

    //     if ($transactionNumber) {
    //         foreach ($allTransactions as $transaction) {
    //             if ($transaction->getDate()->format('Y-m-d') <= $date->format('Y-m-d')) {
    //                 if ($transaction->getTransactionNumber() !== $transactionNumber) {
    //                     $result[] = $transaction;
    //                 }
    //             }
    //         }
    //         foreach ($result as $transaction) {
    //             if ($transaction->getDate()->format('Y-m-d') == $date->format('Y-m-d')) {
    //                 if ($transaction->getTransactionNumber() < $transactionNumber) {
    //                     $result2[] = $transaction;
    //                 }
    //             } else {
    //                 $result3[] = $transaction;
    //             }
    //         }
    //     } else {
    //         foreach ($allTransactions as $transaction) {
    //             if ($transaction->getDate()->format('Y-m-d') <= $date->format('Y-m-d')) {
    //                 $result[] = $transaction;
    //             }
    //         }
    //     }

    //     if (empty($result)) {
    //         return null;
    //     } else {
    //         if (empty($result2) && $result3)
    //             $lastTransaction = $result3[0];
    //         else if ($result2) {
    //             $lastTransaction = $result2[0];
    //         } else {
    //             $lastTransaction = $result[0];
    //         }

    //         $balance = '';

    //         if ($lastTransaction->getTransactionType()->getName() == 'Currency exchange') {
    //             if ($account == $lastTransaction->getTransferFromAccount()) {
    //                 $balance = $lastTransaction->getBalanceTransferFromAccount();
    //             } else if ($account == $lastTransaction->getTransferToAccount()) {
    //                 $balance = $lastTransaction->getBalanceTransferToAccount();
    //             }
    //         } else {
    //             if ($account == $lastTransaction->getMainAccount()) {
    //                 $balance = $lastTransaction->getBalanceMainAccount();
    //             } else if ($account == $lastTransaction->getTransferFromAccount()) {
    //                 $balance = $lastTransaction->getBalanceTransferFromAccount();
    //             } else if ($account == $lastTransaction->getTransferToAccount()) {
    //                 $balance = $lastTransaction->getBalanceTransferToAccount();
    //             }
    //         }

    //         return $balance;
    //     }
    // }

    // public function updateLaterTransactionsBalances(
    //     Account $account,
    //     Transaction $transaction,
    //     DateTimeInterface $date,
    //     string $amount,
    //     bool $increase
    // ): void
    // {
        // $transactions = $this->getTransactionsForUpdateTransaction(
        //     $account, $date, $transaction->getTransactionNumber());

    //     foreach ($transactions as $transaction) {
    //         if ($transaction->getTransactionType()->getName() == 'Funds transfer') {
    //             if ($account == $transaction->getMainAccount()) {
    //                 $transaction->setBalanceMainAccount($amount, $increase);
    //                 $transaction->setBalanceTransferToAccount($amount, $increase);
    //             }
            // } else if ($transaction->getTransactionType()->getName() == 'Bank transfer'
            //     || $transaction->getTransactionType()->getName() == 'Cash transfer') {
                // if ($account == $transaction->getMainAccount()
                //     && $account == $transaction->getTransferFromAccount()) {
            //         $transaction->setBalanceMainAccount($amount, $increase);
            //         $transaction->setBalanceTransferFromAccount($amount, $increase);
                // } else if ($account == $transaction->getMainAccount()
                //     && $account == $transaction->getTransferToAccount()) {
            //         $transaction->setBalanceMainAccount($amount, $increase);
            //         $transaction->setBalanceTransferToAccount($amount, $increase);
            //     } else if ($account == $transaction->getTransferToAccount()) {
            //         $transaction->setBalanceTransferToAccount($amount, $increase);
            //     } else {
            //         $transaction->setBalanceTransferFromAccount($amount, $increase);
            //     }
            // } else if ($transaction->getTransactionType()->getName()
            //     == 'Currency exchange') {
    //             if ($account == $transaction->getTransferFromAccount()) {
    //                 /* TODO: remove if statement after all fields in
                        // database are filled with Balance Main Account for Currency exchange */
    //                 if ($transaction->getBalanceMainAccount() !== null) {
    //                     $transaction->setBalanceMainAccount($amount, $increase);
    //                 }
    //                 $transaction->setBalanceTransferFromAccount($amount, $increase);
    //             } else if ($account == $transaction->getTransferToAccount()) {
    //                 $transaction->setBalanceTransferToAccount($amount, $increase);
    //             }
    //         } else if ($transaction->getTransactionType()->getName() == 'Cash withdrawal') {
    //             if ($account == $transaction->getTransferFromAccount()) {
    //                 $transaction->setBalanceTransferFromAccount($amount, $increase);
    //             } else if ($account == $transaction->getTransferToAccount()) {
    //                 $transaction->setBalanceMainAccount($amount, $increase);
    //                 $transaction->setBalanceTransferToAccount($amount, $increase);
    //             }
    //         } else {
    //             $transaction->setBalanceMainAccount($amount, $increase);
    //         }
    //     }
    // }

    // public function getAllAccountTransactionsDesc(Account $account) :?array
    // {
    //     /** @var TransactionRepository $transactionRepository */
    //     $transactionRepository = $this->enitityManager->getRepository(Transaction::class);

    //     $qb = $transactionRepository->createQueryBuilder('t');

    //     $query = $qb
    //         ->where('t.mainAccount = :account')
    //         ->orWhere('t.transferFromAccount = :account')
    //         ->orWhere('t.transferToAccount = :account')
    //         ->orderBy('t.date', 'DESC')
    //         ->addOrderBy('t.transactionNumber', 'DESC')
    //         ->setParameter('account', $account->getId())
    //     ;

    //     $allTransactions = $query->getQuery()->getResult();

    //     return $allTransactions;
    // }
}
