<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\BudgetRepository;
use App\Entity\Budget;
use App\Entity\BudgetItem;
use App\Repository\BudgetItemRepository;
use App\Service\GeneratePdfDocumentService;
use App\Util\CommonUtil;
use App\Util\EmailUtil;
use App\Util\PDFUtil;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Routing\Annotation\Route;

class SendMailController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GeneratePdfDocumentService $generatePdfDocumentService
    ) {
    }

    // /**
    //  * @Route("/sendEmailAction", name="send_email")
    //  *
    //  * @throws Exception
    //  */
    // public function sendEmailAction(
    //     Request $request,
    //     EmailUtil $emailUtil,
    //     string $projectDir,
    //     CommonUtil $commonUtil,
    //     ValidatorInterface $validator
    // ): JsonResponse
    // {
    //     /** @var BudgetRepository $budgetRepository */
    //     $budgetRepository = $this->entityManager->getRepository(Budget::class);
    //     /** @var BudgetItemRepository $budgetItemRepository */
    //     $budgetItemRepository = $this->entityManager->getRepository(BudgetItem::class);

    //     $data = json_decode($request->getContent(), true);

    //     $constraints = new Assert\Collection([
    //         'fields' => [
    //             'objectId' => new Assert\NotBlank(),
    //             'instanceOf' => new Assert\NotBlank() // NOTE: optional: new Assert\Choice(['Budget', 'BudgetItem']),
    //         ],
    //         'allowExtraFields' => true,
    //     ]);

    //     $violations = $validator->validate($data, $constraints);

    //     if (count($violations) > 0) {
    //         throw new Exception('Validation error on getting data from the send_email request.');
    //     } else {
    //         $objectId = $data['objectId'] ?? null;
    //         $instanceOf = $data['instanceOf'] ?? null;

    //         $recipients = [];

    //         if (!empty($data['formData'])) {
    //             foreach ($data['formData'] as $formData) {
    //                 $recipients[] = $formData['value'];
    //             }
    //         }

    //         switch ($instanceOf) {
    //             case 'Budget':
    //                 $object = $budgetRepository->findOneBy(['id' => $objectId]);

    //                 break;
    //             case 'BudgetItem':
    //                 $object = $budgetItemRepository->findOneBy(['id' => $objectId]);

    //                 break;
    //             default:
    //                 $object = null;
    //         }

    //         if (!$object) {
    //             throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $objectId));
    //         }

    //         // remove invalid email addresses
    //         $validRecipients = $this->getValidRecipients($recipients);

    //         if (empty($validRecipients)) {
    //             return new JsonResponse([
    //                 'status' => 'error',
    //                 'message' => 'You need to provide at least one email address',
    //                 'validRecipients' => $validRecipients,
    //             ], Response::HTTP_UNPROCESSABLE_ENTITY);
    //         }

    //         // generate PDF document
    //         list($pageFormat, $html, $headerHtml, $footerHtml, $filename) =
    //             $this->generatePdfDocumentService->generatePdfDocument($object, $commonUtil);

    //         $pdfUtil = new PDFUtil($projectDir);

    //         // get filename to be used for email attachment
    //         $fileName = sha1(uniqid(random_bytes(40), true)) . '.pdf';
    //         $fileSaveDir = $projectDir . '/' . 'var' . '/' . 'generated_pdf' . '/';

    //         if (false === file_exists($fileSaveDir)) {
    //             mkdir($fileSaveDir, 0755);
    //         }

    //         $saveFilePath = $fileSaveDir . $fileName;

    //         // generate PDF for FR
    //         $pdfUtil->getPdfWkHtmlTopdf(
    //             $html,
    //             $filename,
    //             [
    //                 'title' => (string)$object,
    //                 'page_format' => $pageFormat,
    //                 'footer-html' => $footerHtml,
    //                 'header-html' => $headerHtml,
    //                 'margin-top' => '20mm',
    //                 'margin-bottom' => '15mm',
    //             ],
    //             false,
    //             $saveFilePath,
    //             true
    //         );

    //         // send email
    //         $emailUtil->sendEmail($validRecipients, "A document is attached", (string)$object, [$saveFilePath]);

    //         // delete temporary file
    //         if (file_exists($saveFilePath)) {
    //             unlink($saveFilePath);
    //         }

    //         return new JsonResponse([
    //             'status' => 'success',
    //             'message' => 'Document has been sent.',
    //             'validRecipients' => $validRecipients,
    //         ]);
    //     }
    // }

    // private function getValidRecipients(array $recipients): array
    // {
    //     $validator = Validation::createValidator();

    //     return array_filter($recipients, function ($val) use ($validator) {
    //         $notBlankConstraint = new NotBlank([]);
    //         $emailConstraint = new Email([]);

    //         $errors = $validator->validate($val, $emailConstraint);

    //         if (count($errors)) {
    //             return false;
    //         }

    //         $errors = $validator->validate($val, $notBlankConstraint);

    //         if (count($errors)) {
    //             return false;
    //         }

    //         return true;
    //     });
    // }
}
