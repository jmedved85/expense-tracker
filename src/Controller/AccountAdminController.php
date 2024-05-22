<?php

declare(strict_types=1);

namespace App\Controller;

use App\Admin\AccountAdmin;
use App\Entity\Account;
use App\Entity\TransactionType;
use App\Repository\AccountRepository;
use App\Service\ErrorHandler;
use App\Service\TransactionService;
use App\Traits\ControllerTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TypeError;

final class AccountAdminController extends CRUDController
{
    use ControllerTrait;

    public function __construct(
        private TransactionService $transactionService,
        private ErrorHandler $errorHandler,
        private EntityManagerInterface $entityManager
    ) {
    }

    /** @var AccountAdmin */
    protected $admin;

    public function redirectToPurchaseAction(string $id): RedirectResponse
    {
        /** @var Account $object */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        $accountId = $object->getId();
        $accountName = $object->getNameWithCurrency();
        $accountType = $object->getAccountTypeName();
        $accountCurrency = $object->getCurrency();
        $accountBalance = $object->getBalance();
        $unitId = $object->getUnit()->getId();

        return $this->redirectToRoute('admin_app_purchase_create', [
            'accountId' => $accountId,
            'accountName' => $accountName,
            'accountType' => $accountType,
            'accountCurrency' => $accountCurrency,
            'accountBalance' => $accountBalance,
            'unitId' => $unitId,
            'redirect' => true
        ]);
    }

    public function redirectToCashTransferAction(string $id): RedirectResponse
    {
        /** @var Account $object */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        $accountId = $object->getId();
        $accountName = $object->getNameWithCurrency();
        $accountType = $object->getAccountTypeName();
        $accountCurrency = $object->getCurrency();
        $accountBalance = $object->getBalance();
        $unitId = $object->getUnit()->getId();

        return $this->redirectToRoute('cash_transfer_create', [
            'accountId' => $accountId,
            'accountName' => $accountName,
            'accountType' => $accountType,
            'accountCurrency' => $accountCurrency,
            'accountBalance' => $accountBalance,
            'unitId' => $unitId,
            'redirect' => true
        ]);
    }

    public function redirectToCashWithdrawalAction(string $id): RedirectResponse
    {
        /** @var Account $object */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        $accountId = $object->getId();
        $accountName = $object->getNameWithCurrency();
        $accountTypeId = $object->getAccountType();
        $accountTypeName = $object->getAccountTypeName();
        $accountCurrency = $object->getCurrency();
        $accountBalance = $object->getBalance();
        $unitId = $object->getUnit()->getId();

        return $this->redirectToRoute('cash_withdrawal_create', [
            'accountId' => $accountId,
            'accountName' => $accountName,
            'accountTypeId' => $accountTypeId,
            'accountType' => $accountTypeName,
            'accountCurrency' => $accountCurrency,
            'accountBalance' => $accountBalance,
            'unitId' => $unitId,
            'redirect' => true
        ]);
    }

    public function redirectToCurrencyExchangeAction(string $id): RedirectResponse
    {
        /** @var Account $object */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        $accountId = $object->getId();
        $accountName = $object->getNameWithCurrency();
        $accountTypeId = $object->getAccountType();
        $accountTypeName = $object->getAccountTypeName();
        $accountCurrency = $object->getCurrency();
        $accountBalance = $object->getBalance();
        $unitId = $object->getUnit()->getId();

        return $this->redirectToRoute('currency_exchange_create', [
            'accountId' => $accountId,
            'accountName' => $accountName,
            'accountTypeId' => $accountTypeId,
            'accountType' => $accountTypeName,
            'accountCurrency' => $accountCurrency,
            'accountBalance' => $accountBalance,
            'unitId' => $unitId,
            'redirect' => true
        ]);
    }

    public function redirectToBankTransferAction(string $id): RedirectResponse
    {
        /** @var Account $object */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        $accountId = $object->getID();
        $accountName = $object->getNameWithCurrency();
        $accountTypeId = $object->getAccountType();
        $accountTypeName = $object->getAccountTypeName();
        $accountCurrency = $object->getCurrency();
        $accountBalance = $object->getBalance();
        $unitId = $object->getUnit()->getId();

        return $this->redirectToRoute('bank_transfer_create', [
            'accountId' => $accountId,
            'accountName' => $accountName,
            'accountTypeId' => $accountTypeId,
            'accountType' => $accountTypeName,
            'accountCurrency' => $accountCurrency,
            'accountBalance' => $accountBalance,
            'unitId' => $unitId,
            'redirect' => true
        ]);
    }

    public function addFundsLinkToModalAction(string $id): Response
    {
        /** @var Account $object */
        $object = $this->admin->getSubject();

        $template = 'Account/add_funds_modal_form.html.twig';

        return $this->render($template, [
            'object' => $object,
        ]);
    }

    public function addFundsAction(string $id, Request $request, Session $session): RedirectResponse
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->entityManager->getRepository(Account::class);
        $accountFrom = null;
        $accountFromCurrency = '';
        $balanceTransferFrom = null;

        $dateInput = $request->request->get('addFundsDate');
        $accountFromSelect = $request->request->get('accountSelect');
        $funds = $request->request->get('addFundsAmountInput');
        $realAmount = $request->request->get('addFundsRealAmountInput');
        $description = $request->request->get('addFundsDescription');

        $accountTo = $this->admin->getSubject();
        $accountToType = $accountTo->getAccountTypeName();
        $accountToCurrency = $accountTo->getCurrency();

        if ($accountToType !== 'Cash' && $accountFromSelect !== null) {
            $accountFromSelectCurrency = explode('~', $accountFromSelect);
            $accountId = strval($accountFromSelectCurrency[0]);
            $accountFrom = $accountRepository->findOneBy(['id' => $accountId]);
            $accountFromCurrency = $accountFrom->getCurrency();
            $transactionType = TransactionType::BANK_TRANSFER;
        } else {
            $transactionType = TransactionType::CASH_TRANSFER;
        }

        $unit = $accountTo->getUnit();
        $date = DateTime::createFromFormat('Y-m-d', $dateInput);
        $userAndDateTime = $this->admin->getUserAndDateTime();

        if (strpos($funds, ',')) {
            $funds = str_replace(',', '', $funds);
        }

        if (strpos($realAmount, ',')) {
            $realAmount = str_replace(',', '', $realAmount);
        }

        if ($funds && $dateInput) {
            try {
                $this->entityManager->beginTransaction(); // Start a transaction

                $accountTo->setBalance($funds, true);
                $accountRepository->add($accountTo, true);

                if (!($accountTo->getAccountTypeName() == 'Cash')) {
                    if (($accountToCurrency !== $accountFromCurrency) && $realAmount) {
                        $accountFrom->setBalance($realAmount, false);
                    } else {
                        $accountFrom->setBalance($funds, false);
                    }

                    $accountRepository->add($accountFrom, true);
                }

                $this->entityManager->commit(); // Commit the transaction
            } catch (TypeError | Exception $ex) {
                $this->errorHandler->handleDatabaseErrors($ex);

                throw $ex;
            }

            if ($accountToType !== 'Cash' && $accountFromSelect !== null) {
                $lastTransactionBalanceTransferFrom = $this->admin->getLastTransactionBalance($date, $accountFrom);

                if (($accountToCurrency !== $accountFromCurrency) && $realAmount) {
                    $balanceTransferFrom = floatval($lastTransactionBalanceTransferFrom) - floatval($realAmount);
                } else {
                    $balanceTransferFrom = floatval($lastTransactionBalanceTransferFrom) - floatval($funds);
                }
            }

            $lastTransactionBalanceTransferTo = $this->admin->getLastTransactionBalance($date, $accountTo);
            $balanceTransferTo = floatval($lastTransactionBalanceTransferTo) + floatval($funds);

            /* New record in transaction table */
            $transaction = $this->transactionService->createAddFundsTransaction(
                $date,
                $unit,
                $transactionType,
                $accountFrom,
                $accountTo,
                $funds,
                $realAmount,
                $description,
                $userAndDateTime,
                $balanceTransferFrom,
                $balanceTransferTo,
            );

            $this->updateLaterTransactionsBalances($transaction, $date, $funds, true);

            if ($accountToType !== 'Cash' && $accountFromSelect !== null) {
                if (($accountToCurrency !== $accountFromCurrency) && $realAmount) {
                    $this->updateLaterTransactionsBalancesTransferFromAccount($transaction, $date, $realAmount, false);
                } else {
                    $this->updateLaterTransactionsBalancesTransferFromAccount($transaction, $date, $funds, false);
                }
            }
        } elseif ($funds && $dateInput == null) {
            $session->getFlashBag()->add('error', 'Please, pick a date');
        } elseif ($funds == null && $dateInput) {
            $session->getFlashBag()->add('error', 'Please, enter a value');
        } elseif ($funds == null && $dateInput == null) {
            $session->getFlashBag()->add('error', 'Please, enter a value and pick a date');
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
