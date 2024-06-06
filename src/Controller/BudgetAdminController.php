<?php

declare(strict_types=1);

namespace App\Controller;

use App\Admin\BudgetAdmin;
use App\Entity\Budget;
use App\Repository\BudgetRepository;
use App\Service\GeneratePdfDocumentService;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BudgetAdminController extends CRUDController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GeneratePdfDocumentService $generatePdfDocumentService
    ) {
    }

    // public function emailModalAction(string $id): Response
    // {
    //     $object = $this->admin->getSubject();

    //     $template = 'email/email_modal_form.html.twig';

    //     return $this->render($template, [
    //         'object' => $object,
    //     ]);
    // }

    // /**
    //  * @throws NotFoundHttpException|Exception
    //  */
    // public function viewDocumentAction(
    //     string $id,
    //     Request $request,
    //     string $projectDir,
    //     CommonUtil $commonUtil
    // ): Response
    // {
    //     /** @var Budget|null $object */
    //     $object = $this->admin->hasSubject() ? $this->admin->getSubject() : null;

    //     if (!$object) {
    //         throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
    //     }

    //     $download = (bool)$request->query->getInt('download');

    //     list($pageFormat, $html, $headerHtml, $footerHtml, $filename, $download)
    //         = $this->generatePdfDocumentService->generatePdfDocument($object, $commonUtil, $download);

    //     $pdfUtil = new PDFUtil($projectDir);

    //     return $pdfUtil->getPdfWkHtmlTopdf(
    //         $html,
    //         $filename,
    //         [
    //             'title' => (string)$object,
    //             'page_format' => $pageFormat,
    //             'footer-html' => $footerHtml,
    //             'header-html' => $headerHtml,
    //             'margin-top' => '20mm',
    //             'margin-bottom' => '15mm',
    //         ],
    //         $download
    //     );
    // }

    protected function preShow(Request $request, object $object): ?Response
    {
        /** @var BudgetRepository $budgetRepository */
        $budgetRepository = $this->entityManager->getRepository(Budget::class);

        if ($object instanceof Budget) {
            $budget = $budgetRepository->findWithTotals($object->getId());

            $this->admin->setSubject($budget);
        }

        return null;
    }
}
