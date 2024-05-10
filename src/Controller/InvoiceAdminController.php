<?php

declare(strict_types=1);

namespace App\Controller;

use App\Admin\InvoiceAdmin;
use App\Entity\Account;
use App\Entity\BudgetMainCategory;
use App\Entity\BudgetSubCategory;
use App\Entity\Invoice;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Repository\AccountRepository;
use App\Repository\InvoiceRepository;
use App\Repository\TransactionRepository;
use App\Service\ErrorHandler;
use App\Service\TransactionService;
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

final class InvoiceAdminController extends CRUDController
{
    // use ControlerTrait;

    private TransactionService $transactionService;
    private ErrorHandler $errorHandler;
    private EntityManagerInterface $entityManager;


    public function __construct(
        TransactionService $transactionService,
        ErrorHandler $errorHandler,
        EntityManagerInterface $entityManager
    ) {
        $this->transactionService = $transactionService;
        $this->errorHandler = $errorHandler;
        $this->entityManager = $entityManager;
    }

    /** @var InvoiceAdmin */
    protected $admin;

    // protected function preCreate(Request $request, object $object): ?Response
    // {
    //     if ($request->getMethod() == 'POST') {
    //         /* Set invoiceType to 'Invoice' */
    //         $object->setInvoiceType('Invoice');
    //     }

    //     return null;
    // }

    // protected function preEdit(Request $request, object $object): ?Response
    // {
    //     // $this->editDelete($request, $object);

    //     if (isset($request->query->all()['uniqid'])) {
    //         $uniqid = $request->query->all()['uniqid'];
    //         $reqUniqid = $request->request->get($uniqid);

    //         $invoiceLinesObj = $object->getInvoiceLines()->toArray();
    //         $invoiceLinesReq = $reqUniqid ? (array)$reqUniqid['invoiceLines'] : null;

    //         $count = count($invoiceLinesObj);

    //         for ($i = 0; $i < $count; $i++) {
    //             // From object
    //             $budgetMainCategoryIdObj =
    //                 $invoiceLinesObj[$i]->getBudgetMainCategory()
    //                 ? $invoiceLinesObj[$i]->getBudgetMainCategory()->getId()
    //                 : null
    //             ;

    //             $budgetSubCategoryIdObj =
    //                 $invoiceLinesObj[$i]->getBudgetCategory()
    //                 ? $invoiceLinesObj[$i]->getBudgetCategory()->getId()
    //                 : null
    //             ;

    //             // From request
    //             $budgetMainCategoryIdReq = $invoiceLinesReq[$i]['budgetMainCategory'] ?? null;
    //             $budgetSubCategoryIdReq = $invoiceLinesReq[$i]['budgetSubCategory'] ?? null;

    //             if ($budgetMainCategoryIdObj !== $budgetMainCategoryIdReq
    //                 && $budgetSubCategoryIdObj !== $budgetSubCategoryIdReq) {
    //                 $budgetMainCategoryRepository = $this->entityManager->getRepository(BudgetMainCategory::class);
                    // $budgetMainCategory = $budgetMainCategoryRepository
                    //     ->findOneBy(['id' => $budgetMainCategoryIdReq]);

    //                 $budgetSubCategoryRepository = $this->entityManager->getRepository(BudgetSubCategory::class);
    //                 $budgetSubCategory = $budgetSubCategoryRepository->findOneBy(['id' => $budgetSubCategoryIdReq]);

    //                 $invoiceLinesObj[$i]->setBudgetMainCategory($budgetMainCategory);
    //                 $invoiceLinesObj[$i]->setBudgetSubCategory($budgetSubCategory);
    //             }
    //         }
    //     }

    //     return null;
    // }

    // /**
    //  * @param string $id
    //  *
    //  * @return Response
    //  */
    // public function commentsAction(string $id): Response
    // {
    //     $commentsList = $this->getComments($id);

    //     $template = 'Comments/comments_view_list.html.twig';

    //     return $this->renderWithExtraParams($template, [
    //         'commentsList' => $commentsList,
    //     ]);
    // }

    /**
     * @param string $id
     *
     * @return Response
     */
    public function addBankFeeLinkToModalAction(string $id): Response
    {
        $object = $this->admin->getSubject();

        $template = 'Invoice/add_bank_fee_modal_form.html.twig';

        return $this->render($template, [
            'object' => $object,
        ]);
    }

    /**
     * @param string $id
     * @param Request $request
     * @param Session $session
     * @return RedirectResponse
     * @throws Exception
     */
    public function addBankFeeAction(string $id, Request $request, Session $session): RedirectResponse
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->entityManager->getRepository(Account::class);
        /** @var InvoiceRepository $invoiceRepository */
        $invoiceRepository = $this->entityManager->getRepository(Invoice::class);
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        $invoice = $this->admin->getSubject();
        $invoiceTransactions = $invoice->getTransactions()->toArray();
        $unit = $invoice->getUnit();
        $account = $invoice->getAccount();
        $funds = $request->request->get('addBankFeeInput');
        $dateInput = $request->request->get('addBankFeeDate');
        $bankFeeNotAddedCheck = $request->request->get('bankFeeNotAddedCheck');
        $userAndDateTime = $this->admin->getUserAndDateTime();
        $bankFeeTransactionType = TransactionType::ACCOUNT_CHARGE;

        $date = DateTime::createFromFormat('Y-m-d', $dateInput);

        if (strpos($funds, ',')) {
            $funds = str_replace(',', '', $funds);
        }

        if ($funds && $dateInput && !$bankFeeNotAddedCheck) {
            if (!empty($invoiceTransactions)) {
                $invoiceTransaction = $invoiceTransactions[0];

                try {
                    $this->entityManager->beginTransaction(); // Start a transaction

                    $account->setBalance($funds, false);
                    $accountRepository->add($account, true);

                    $this->entityManager->commit(); // Commit the transaction

                    $lastTransactionBalance = $this->admin->getLastTransactionBalance($date, $account);
                    $balance = floatval($lastTransactionBalance) - floatval($funds);
                } catch (TypeError | Exception $ex) {
                    $this->errorHandler->handleDatabaseErrors($ex);

                    throw $ex;
                }

                /* New record in transaction table */
                $bankFeeTransaction = $this->transactionService->createOrUpdateBankFeeTransaction(
                    $date,
                    $unit,
                    $bankFeeTransactionType,
                    $account,
                    $invoiceTransaction,
                    $funds,
                    $userAndDateTime,
                    $balance,
                    'create'
                );

                $this->admin->updateLaterTransactionsBalancesTransaction(
                    $account,
                    $bankFeeTransaction,
                    $date,
                    $funds,
                    false
                );

                try {
                    $this->entityManager->beginTransaction(); // Start a transaction

                    /* Set bank fee in invoice object */
                    $invoice->setBankFeeAdded(true);
                    $invoice->setBankFeeAmount($funds);
                    $invoiceTransaction->setBankFeeAdded(true);
                    $invoiceTransaction->setBankFeeAmount($funds);
                    $invoiceTransaction->setBankFeeCurrency($bankFeeTransaction->getCurrency());

                    if ($invoice->isBankFeeNotAdded()) {
                        $invoice->setBankFeeNotAdded(false);
                        $invoiceTransaction->setBankFeeNotAdded(false);
                        $transactionRepository->add($invoiceTransaction, true);
                    }

                    $invoiceRepository->add($invoice, true);

                    $this->entityManager->commit(); // Commit the transaction
                } catch (TypeError | Exception $ex) {
                    $this->errorHandler->handleDatabaseErrors($ex);

                    throw $ex;
                }
            }
        } elseif (!$bankFeeNotAddedCheck) {
            if ($funds && $dateInput == null) {
                $session->getFlashBag()->add('error', 'Please, pick a date');
            } elseif ($funds == null && $dateInput) {
                $session->getFlashBag()->add('error', 'Please, enter a value');
            } elseif ($funds == null && $dateInput == null) {
                $session->getFlashBag()->add('error', 'Please, enter a value and pick a date');
            }
        } else {
            if (!$invoice->isBankFeeNotAdded()) {
                if (!empty($invoiceTransactions)) {
                    $invoiceTransaction = $invoiceTransactions[0];

                    try {
                        $this->entityManager->beginTransaction(); // Start a transaction

                        $invoice->setBankFeeNotAdded(true);
                        $invoiceTransaction->setBankFeeNotAdded(true);

                        $invoiceRepository->add($invoice, true);
                        $transactionRepository->add($invoiceTransaction, true);

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

    /**
     * @param string $id
     * @return RedirectResponse
     */
    public function redirectToMoneyReturnAction(string $id): RedirectResponse
    {
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        $bankPaymentTransaction = null;

        foreach ($object->getTransactions() as $transaction) {
            if (
                $transaction->getTransactionType()->getName() == 'Bank payment'
                && $transaction->getMoneyReturnedAmount() == null
            ) {
                $bankPaymentTransaction = $transaction;
            }
        }

        return $this->redirectToRoute('money_return_create', [
            'invoiceId' => $object->getId(),
            'transactionId' => $bankPaymentTransaction->getId(),
            'redirect' => true
        ]);
    }
}
