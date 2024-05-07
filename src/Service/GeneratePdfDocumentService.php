<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\BudgetRepository;
use App\Repository\BudgetItemRepository;
use App\Repository\UnitRepository;
use App\Entity\Budget;
use App\Entity\BudgetItem;
use App\Entity\Unit;
use App\Utility\AppUtil;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

class GeneratePdfDocumentService
{
    public function __construct(private EntityManagerInterface $entityManager, private Environment $twig)
    {
    }

    public function generatePdfDocument(object $object, AppUtil $appUtil, bool $download = false): array
    {
        /** @var BudgetRepository $budgetRepository */
        $budgetRepository = $this->entityManager->getRepository(Budget::class);
        /** @var BudgetItemRepository $budgetItemRepository */
        $budgetItemRepository = $this->entityManager->getRepository(BudgetItem::class);

        $unit = $object->getUnit();

        $templateRoute = '';
        $budgetInvoices = [];
        $budgetPurchases = [];

        if ($object instanceof Budget) {
            $object = $budgetRepository->findWithTotals($object->getId());
            $budgetInvoices = $budgetRepository->findBudgetInvoices($object->getId());
            $budgetPurchases = $budgetRepository->findBudgetPurchases($object->getId());

            $templateRoute = 'PDF/budget.html.twig';
        } elseif ($object instanceof BudgetItem) {
            $budgetInvoices = $budgetItemRepository->findBudgetItemInvoices($object->getId());
            $budgetPurchases = $budgetItemRepository->findBudgetItemPurchases($object->getId());

            $templateRoute = 'PDF/budget.html.twig';
        }

        $title = $object->__toString();
        $pageFormat = 'A4';

        // generate HTML
        $html = $this->twig->render($templateRoute, [
            'page_format' => $pageFormat,
            'title' => $title,
            'object' => $object ?? null,
            'budgetInvoices' => $budgetInvoices,
            'budgetPurchases' => $budgetPurchases,
        ]);

        // header
        $headerHtml = $appUtil->getHeaderHtml($unit, new DateTime());
        // footer
        $footerHtml = $appUtil->getFooterHtml($unit);

        $filename = $title . '.pdf';
        $filename = str_replace(' ', '_', $filename);
        $filename = preg_replace('/[^a-z0-9_.]/', '', strtolower($filename));

        return array($pageFormat, $html, $headerHtml, $footerHtml, $filename, $download);
    }

    public function generatePdfReport(
        array $pdfData,
        array $pdfDataItem,
        string $reportGroup,
        AppUtil $appUtil,
        bool $download = false
    ): array
    {
        /** @var UnitRepository $unitRepository */
        $unitRepository = $this->entityManager->getRepository(Unit::class);

        $title = $pdfData['reportType'] . ' Report';
        $dateStart = $pdfData['dateStart'];
        $dateEnd = $pdfData['dateEnd'];
        $month = $pdfData['month'];
        $monthYear = $pdfData['monthYear'];
        $period = $pdfData['period'];
        $periodYear = $pdfData['periodYear'];
        $pdfDataItemName = !empty($pdfDataItem) ? $pdfDataItem['name'] : null;
        $unit = $unitRepository->findOneBy(['id' => $pdfData['unitId']]);

        if ($dateStart && $dateEnd) {
            $subTitle = $dateStart . ' - ' . $dateEnd;
        } else if ($month) {
            $subTitle = $month . ' ' . $monthYear;
        } else {
            $subTitle = $period . ' ' . $periodYear;
        }

        $pageFormat = 'A4';

        /* generate HTML */
        $html = $this->twig->render('PDF/generated_report.html.twig', [
            'page_format' => $pageFormat,
            'title' => $title,
            'subTitle' => $subTitle,
            'reportType' => $pdfData['reportType'],
            'selected' => $pdfData['selected'],
            'data' => !empty($pdfDataItem) ? $pdfDataItem : $pdfData,
            'reportGroup' => $reportGroup,
            'pdfDataItemName' => $pdfDataItemName ?? null,
            'withTransactionsCheck' => true
        ]);

        // header
        $headerHtml = $appUtil->getHeaderHtml($unit, new DateTime());
        // footer
        $footerHtml = $appUtil->getFooterHtml($unit);

        $filename = $title . ' ' . $subTitle . '.pdf';
        $filename = str_replace(' ', '_', $filename);
        $filename = preg_replace('/[^a-z0-9_.]/', '', strtolower($filename));

        return array($title, $pageFormat, $html, $headerHtml, $footerHtml, $filename, $download);
    }
}