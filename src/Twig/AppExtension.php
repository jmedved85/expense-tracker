<?php

namespace App\Twig;

use App\Entity\Account;
use App\Entity\AccountType;
use App\Entity\Invoice;
use App\Entity\Transaction;
use App\Entity\Unit;
use App\Repository\AccountRepository;
use App\Repository\InvoiceRepository;
use App\Repository\UnitRepository;
use App\Utility\AppUtil;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\Pool;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private Pool $configurationPool,
        private AppUtil $appUtil,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function getFilters()
    {
        return [];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_user', [$this, 'getUser']),
            new TwigFunction('is_switched_into_unit', [$this, 'isSwitchedIntoUnit']),
            new TwigFunction('get_switched_unit', [$this, 'getSwitchedUnit']),
            new TwigFunction('get_switched_unit_id', [$this, 'getSwitchedUnitId']),
            new TwigFunction('get_unit_list', [$this, 'getUnitList']),
            new TwigFunction('get_accounts_totals_menu', [$this, 'getAccountsTotalsMenu']),
            new TwigFunction('get_invoice_list_headers', [$this, 'getInvoiceListHeaders']),
            new TwigFunction('get_approved_invoices', [$this, 'getApprovedInvoices']),
        ];
    }

    public function getUser(): ?array
    {
        return $this->appUtil->getCurrentUserData();
    }

    public function isSwitchedIntoUnit(): ?bool
    {
        return (bool) $this->getSwitchedUnit();
    }

    public function getSwitchedUnit(): ?Unit
    {
        return $this->appUtil->getSwitchedUnit();
    }

    public function getSwitchedUnitId(): ?int
    {
        return $this->appUtil->getSwitchedUnitId();
    }

    public function getUnitList(): array
    {
        /** @var UnitRepository $unitRepository */
        $unitRepository = $this->entityManager->getRepository(Unit::class);
        $currentUserData = $this->appUtil->getCurrentUserData();

        if ($currentUserData['isSuperAdmin']) {
            $units = $unitRepository->findBy([
                'active' => true
            ], ['name' => 'ASC']);
        } else {
            $units = $currentUserData['userUnits'];
        }

        return $units;
    }

    public function getAccountsTotalsMenu(): ?array
    {
        $accounts = [];
        $currencies = $this->getCurrencyListFromAccounts();

        foreach ($currencies as $currency) {
            $bankAccountsData = $this->getBankAccountsData($currency);
            $cardAccountsData = $this->getCardAccountsData($currency);
            $cashAccountsData = $this->getCashAccountsData($currency);

            // Calculate the total balance for the current currency
            $bankTotalBalance = 0;
            $cardTotalBalance = 0;
            $cashTotalBalance = 0;

            foreach ($bankAccountsData as $accountData) {
                $bankTotalBalance += $accountData['balance'];
            }

            foreach ($cardAccountsData as $accountData) {
                $cardTotalBalance += $accountData['balance'];
            }

            foreach ($cashAccountsData as $accountData) {
                $cashTotalBalance += $accountData['balance'];
            }

            // Add the totals field to the bankAccounts subarray
            $accounts[$currency] = [
                'bankAccounts' => $bankAccountsData,
                'bankTotals' => $bankTotalBalance,
                'cardAccounts' => $cardAccountsData,
                'cardTotals' => $cardTotalBalance,
                'cashAccounts' => $cashAccountsData,
                'cashTotals' => $cashTotalBalance,
            ];
        }

        return $accounts;
    }

    public function getInvoiceListHeaders(): ?array
    {
        $adminCode = 'admin.invoice';

        return $this->getHeaderLabels($adminCode);
    }

    public function getApprovedInvoices(): ?array
    {
        /** @var InvoiceRepository $invoiceRepository */
        $invoiceRepository = $this->entityManager->getRepository(Invoice::class);

        $currentUserData = $this->appUtil->getCurrentUserData();

        /* Get unit id if unit is switched */
        $switchedIntoUnitId = $this->appUtil->getSwitchedUnitId();

        $qb = $invoiceRepository->createQueryBuilder('i');

        $userUnits = $currentUserData['userUnits'];
        $userUnitsIds = [];

        foreach ($userUnits as $unit) {
            array_push($userUnitsIds, $unit->getId());
        }

        if ($switchedIntoUnitId) {
            $result = $qb
                ->where('i.invoicePaymentStatus = :invoicePaymentStatusUnpaid')
                ->andWhere(
                    $qb->expr()->orX(
                        'i.invoiceApprovalStatus = :invoiceApprovalStatusApproved',
                    )
                )
                ->andWhere('i.unit = :unit')
                ->orderBy('i.invoiceDate', 'DESC')
                ->setParameter('invoicePaymentStatusUnpaid', 'Unpaid')
                ->setParameter('invoiceApprovalStatusApproved', 'Approved')
                ->setParameter('unit', $switchedIntoUnitId)
                ->getQuery()
                ->getResult()
            ;
        } else {
            $result = $qb
                ->join('i.unit', 's')
                ->where('i.invoicePaymentStatus = :invoicePaymentStatusUnpaid')
                ->andWhere(
                    $qb->expr()->orX(
                        'i.invoiceApprovalStatus = :invoiceApprovalStatusApproved',
                    )
                )
                ->andWhere('s.active = :active')
                ->orderBy('i.invoiceDate', 'DESC')
                ->setParameter('invoicePaymentStatusUnpaid', 'Unpaid')
                ->setParameter('invoiceApprovalStatusApproved', 'Approved')
                ->setParameter('active', true)
                ->getQuery()
                ->getResult()
            ;
        }

        return $result;
    }

    private function getCurrencyListFromAccounts(): ?array
    {
        /* Get unit id if unit is switched */
        $switchedIntoUnitId = $this->appUtil->getSwitchedUnitId();

        if ($switchedIntoUnitId) {
            $query = $this->entityManager->createQueryBuilder()
                ->select('DISTINCT a.currency')
                ->from(Account::class, 'a')
                ->where('a.unit = :unitId')
                ->andWhere('a.deactivated = :active')
                ->orderBy('a.currency', 'ASC')
                ->setParameter('unitId', $switchedIntoUnitId)
                ->setParameter('active', false)
                ->getQuery()
            ;
        } else {
            $query = $this->entityManager->createQuery(
                'SELECT DISTINCT a.currency FROM ' . Transaction::class . ' a ORDER BY a.currency ASC'
            );
        }

        $currenciesFromAccounts = $query->getResult();

        $currencies = [];

        foreach ($currenciesFromAccounts as $currency) {
            array_push($currencies, array_values($currency)[0]);
        }

        return $currencies;
    }

    private function getBankAccountsData(string $currency = null): array
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->entityManager->getRepository(Account::class);
        $bankAccountType = AccountType::BANK;

        /* Get unit id if unit is switched */
        $switchedIntoUnitId = $this->appUtil->getSwitchedUnitId();

        $qb = $accountRepository->createQueryBuilder('a');

        $bankAccountsData = [];

        if ($switchedIntoUnitId) {
            $accounts = $qb
                ->select('a.id, a.name, a.balance')
                ->where('a.accountType = :bankAccountType')
                ->andWhere('a.currency = :currency')
                ->andWhere('a.unit = :unit')
                ->andWhere('a.deactivated = false')
                ->orderBy('a.name', 'ASC')
                ->setParameter('bankAccountType', $bankAccountType)
                ->setParameter('currency', $currency)
                ->setParameter('unit', $switchedIntoUnitId)
                ->getQuery()
                ->getResult()
            ;

            foreach ($accounts as $account) {
                $accountId = $account['id'];

                $bankAccountsData[$accountId] = [
                    'id' => $account['id'],
                    'name' => $account['name'],
                    'balance' => $account['balance'],
                ];
            }
        }

        return $bankAccountsData;
    }

    private function getCardAccountsData(string $currency = null): array
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->entityManager->getRepository(Account::class);
        $cardAccountType = AccountType::DEBIT_CARD;

        /* Get unit id if unit is switched */
        $switchedIntoUnitId = $this->appUtil->getSwitchedUnitId();

        $qb = $accountRepository->createQueryBuilder('a');

        $cardAccountsData = [];

        if ($switchedIntoUnitId) {
            $accounts = $qb
                ->select('a.id, a.name, a.balance')
                ->where('a.accountType = :cardAccountType')
                ->andWhere('a.currency = :currency')
                ->andWhere('a.unit = :unit')
                ->andWhere('a.deactivated = false')
                ->orderBy('a.name', 'ASC')
                ->setParameter('cardAccountType', $cardAccountType)
                ->setParameter('currency', $currency)
                ->setParameter('unit', $switchedIntoUnitId)
                ->getQuery()
                ->getResult()
            ;

            foreach ($accounts as $account) {
                $accountId = $account['id'];

                $cardAccountsData[$accountId] = [
                    'id' => $account['id'],
                    'name' => $account['name'],
                    'balance' => $account['balance'],
                ];
            }
        }

        return $cardAccountsData;
    }

    private function getCashAccountsData(string $currency = null): array
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->entityManager->getRepository(Account::class);
        $cashAccountType = AccountType::CASH;

        /* Get unit id if unit is switched */
        $switchedIntoUnitId = $this->appUtil->getSwitchedUnitId();

        $qb = $accountRepository->createQueryBuilder('a');

        $cashAccountsData = [];

        if ($switchedIntoUnitId) {
            $accounts = $qb
                ->select('a.id, a.name, a.balance')
                ->where('a.accountType = :cashAccountType')
                ->andWhere('a.currency = :currency')
                ->andWhere('a.unit = :unit')
                ->andWhere('a.deactivated = false')
                ->orderBy('a.name', 'ASC')
                ->setParameter('cashAccountType', $cashAccountType)
                ->setParameter('currency', $currency)
                ->setParameter('unit', $switchedIntoUnitId)
                ->getQuery()
                ->getResult()
            ;

            foreach ($accounts as $account) {
                $accountId = $account['id'];

                $cashAccountsData[$accountId] = [
                    'id' => $account['id'],
                    'name' => $account['name'],
                    'balance' => $account['balance'],
                ];
            }
        }

        return $cashAccountsData;
    }

    private function getHeaderLabels(string $adminCode): array
    {
        $skipFields = [
            'Email',
            'Doc File(s)',
            'Comments',
            'Date Paid',
            'Date Created',
            'Date Modified',
            'Unit',
            'Unit Name',
            'Paying Bank Account',
            'Date Due
        '];

        $invoiceAdmin = $this->configurationPool->getAdminByAdminCode($adminCode);
        $listMapper = $invoiceAdmin->getListFieldDescriptions();

        $headerLabels = [];

        foreach ($listMapper as $item) {
            $label = $item->getOptions()['label'];

            if (false === in_array($label, $skipFields)) {
                array_push($headerLabels, $label);
            }
        }

        return $headerLabels;
    }
}
