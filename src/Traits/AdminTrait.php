<?php

declare(strict_types=1);

namespace App\Traits;

use App\Entity\Account;
use App\Entity\AccountType;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Entity\User;
use App\Repository\TransactionRepository;
use DateTime;
use DateTimeInterface;
use Exception;
use Symfony\Component\HttpFoundation\Session\Session;

trait AdminTrait
{
    /* TODO: Add functionality to custom user choice in settings */
    protected $preferredCurrencyChoices = ['EUR', 'USD', 'GBP'];

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

    public function getCurrentUserData(): ?array
    {
        /* Get logged in user from the token */
        if ($this->tokenStorage->getToken()) {
            /** @var User $currentUser */
            $currentUser = $this->tokenStorage->getToken()->getUser();

            $currentUserId = $currentUser->getId();
            $currentUserName = $currentUser->getUsername();
            // $unitId = $this->getUnitId();

            $bankAccountType = AccountType::BANK->value;

            $unitObject = null;

            // if ($unitId) {
            //     $unitObject = $this->getUnitById($unitId);
            // }

            /* Get user's role */
            $userRoles = $currentUser->getRoles();

            $data = [];
            $data['user'] = $currentUser;
            $data['userId'] = $currentUserId;
            $data['userName'] = $currentUserName;
            // $data['unitId'] = $unitId ?? null;
            // $data['unitObject'] = isset($unitObject) ? $unitId : null;
            $data['bankAccountTypeId'] = $bankAccountType;
            $data['userRoles'] = $userRoles;
            $data['isSuperAdmin'] = in_array("ROLE_SUPER_ADMIN", $data['userRoles']);
            // $data['userUnit'] = $userUnit;

            return $data;
        } else {
            return null;
        }
    }

    public function getAutoTransactionNumber(object $unit): ?int
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

    /* TODO: refactor to transaction service class */
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

    public function updateLaterTransactionsBalances(
        Account $account,
        Transaction $transaction,
        DateTimeInterface $date,
        string $amount,
        bool $increase
    ): void {
        $transactions = $this->getTransactionsForUpdate(
            $account,
            $date,
            $transaction->getTransactionNumber()
        );

        foreach ($transactions as $transaction) {
            if (
                $transaction->getTransactionTypeName() == 'Bank Transfer'
                || $transaction->getTransactionTypeName() == 'Cash Transfer'
            ) {
                if (
                    $account == $transaction->getMainAccount()
                    && $account == $transaction->getTransferFromAccount()
                ) {
                    $transaction->setBalanceMainAccount($amount, $increase);
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                } elseif (
                    $account == $transaction->getMainAccount()
                    && $account == $transaction->getTransferToAccount()
                ) {
                    $transaction->setBalanceMainAccount($amount, $increase);
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                } elseif ($account == $transaction->getTransferToAccount()) {
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                } else {
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                }
            } elseif ($transaction->getTransactionTypeName() == 'Currency Exchange') {
                if ($account == $transaction->getTransferFromAccount()) {
                    /* TODO: remove if statement after all fields in
                        database are filled with Balance Main Account for Currency exchange */
                    if ($transaction->getBalanceMainAccount() !== null) {
                        $transaction->setBalanceMainAccount($amount, $increase);
                    }
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                } elseif ($account == $transaction->getTransferToAccount()) {
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                }
            } elseif ($transaction->getTransactionTypeName() == 'Cash Withdrawal') {
                if ($account == $transaction->getTransferFromAccount()) {
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                } elseif ($account == $transaction->getTransferToAccount()) {
                    $transaction->setBalanceMainAccount($amount, $increase);
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                }
            } else {
                $transaction->setBalanceMainAccount($amount, $increase);
            }
        }
    }

    public function updateLaterTransactionsBalancesTransaction(
        Account $account,
        Transaction $transaction,
        DateTimeInterface $date,
        string $amount,
        bool $increase
    ): void {
        $transactions = $this->getTransactionsForUpdate(
            $account,
            $date,
            $transaction->getTransactionNumber()
        );

        foreach ($transactions as $transaction) {
            if (
                $transaction->getTransactionTypeName() == 'Bank Transfer'
                || $transaction->getTransactionTypeName() == 'Cash Transfer'
            ) {
                if (
                    $account == $transaction->getMainAccount()
                    && $account == $transaction->getTransferFromAccount()
                ) {
                    $transaction->setBalanceMainAccount($amount, $increase);
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                } elseif (
                    $account == $transaction->getMainAccount()
                    && $account == $transaction->getTransferToAccount()
                ) {
                    $transaction->setBalanceMainAccount($amount, $increase);
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                } elseif ($account == $transaction->getTransferToAccount()) {
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                } else {
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                }
            } elseif ($transaction->getTransactionTypeName() == 'Currency Exchange') {
                if ($account == $transaction->getTransferFromAccount()) {
                    if ($transaction->getBalanceMainAccount() !== null) {
                        $transaction->setBalanceMainAccount($amount, $increase);
                    }
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                } elseif ($account == $transaction->getTransferToAccount()) {
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                }
            } elseif ($transaction->getTransactionTypeName() == 'Cash Withdrawal') {
                if ($account == $transaction->getTransferFromAccount()) {
                    $transaction->setBalanceTransferFromAccount($amount, $increase);
                } elseif ($account == $transaction->getTransferToAccount()) {
                    $transaction->setBalanceMainAccount($amount, $increase);
                    $transaction->setBalanceTransferToAccount($amount, $increase);
                }
            } else {
                $transaction->setBalanceMainAccount($amount, $increase);
            }
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

        $allTransactions = $query->getQuery()->getResult();

        return $allTransactions;
    }

    public function getTransactionsForUpdate(
        Account $account,
        DateTimeInterface $date,
        int $transactionNumber
    ): ?array {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        $qb = $transactionRepository->createQueryBuilder('t');

        $query = $qb
            ->where('t.mainAccount = :account')
            ->orWhere('t.transferFromAccount = :account')
            ->orWhere('t.transferToAccount = :account')
            ->andWhere('t.date >= :date')
            ->orderBy('t.date', 'ASC')
            ->addOrderBy('t.transactionNumber', 'ASC')
            ->setParameter('account', $account->getId())
            ->setParameter('date', $date->format('Y-m-d'))
        ;

        $transactions = $query->getQuery()->getResult();
        $transactionsForUpdate = [];

        foreach ($transactions as $transaction) {
            if (
                $transaction->getDate()->format('Y-m-d') == $date->format('Y-m-d')
                && $transaction->getTransactionNumber() > $transactionNumber
            ) {
                $transactionsForUpdate[] = $transaction;
            } elseif (
                $transaction->getDate()->format('Y-m-d') > $date->format('Y-m-d')
                && $transaction->getTransactionNumber() !== $transactionNumber
            ) {
                $transactionsForUpdate[] = $transaction;
            }
        }

        return $transactionsForUpdate;
    }
}
