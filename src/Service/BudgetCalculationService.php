<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BudgetItem;
use App\Entity\InvoiceLine;
use App\Entity\PurchaseLine;
use App\Repository\BudgetItemRepository;
use App\Repository\InvoiceLineRepository;
use App\Repository\PurchaseLineRepository;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;

class BudgetCalculationService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function calculateBudgetsActualExpenses(
        object $object,
        string $originalBudgetId = null,
        string $flag = null
    ): void {
        $reflectionClass = new ReflectionClass($object);
        $className = $reflectionClass->getShortName();
        $unitOfWork = $this->entityManager->getUnitOfWork();

        $budgetId = '';

        // Checking if the object has a budget
        if ($object->getBudget()) {
            $budgetId = $object->getBudget()->getId();
        } elseif ($originalBudgetId) {
            $budgetId = $originalBudgetId;
        }

        $currency = $object->getCurrency();
        $unitId = strval($object->getUnit()->getId());

        $objectLines = $className ==
            'Invoice' ? $object->getInvoiceLines()->toArray() : $object->getPurchaseLines()->toArray();

        if ($object->getId()) {
            $objectLineSnapshot = $className ==
            'Invoice' ? $object->getInvoiceLines()->getSnapshot() : $object->getPurchaseLines()->getSnapshot();

             /* (RE)CALCULATE DELETED OR ADDED LINES */
            if ($objectLines !== $objectLineSnapshot) {
                if (count($objectLines) < count($objectLineSnapshot)) {
                    $deletedLines = [];

                    foreach ($objectLineSnapshot as $snapshotLine) {
                        $found = false;

                        foreach ($objectLines as $line) {
                            if ($line->getId() == $snapshotLine->getId()) {
                                $found = true;

                                break;
                            }
                        }

                        if (!$found) {
                            $deletedLines[] = $snapshotLine;
                        }
                    }

                    foreach ($deletedLines as $line) {
                        $originalData = $unitOfWork->getOriginalEntityData($line);

                        if ($line->getBudgetMainCategory() && $line->getBudgetSubCategory()) {
                            $budgetMainCategoryId = $line->getBudgetMainCategory()->getId();
                            $budgetSubCategoryId = $line->getBudgetSubCategory()->getId();
                            $lineTotal = $line->getLineTotal();

                            if ($this->isValidOriginalData($originalData, 'lineTotal')) {
                                if (($originalData['lineTotal']) != $lineTotal) {
                                    $lineTotal = $originalData['lineTotal'];
                                }
                            }

                            $budgetItem = $this->getBudgetItem(
                                $budgetId,
                                $budgetMainCategoryId,
                                $budgetSubCategoryId,
                                $currency,
                                $unitId
                            );

                            if ($budgetItem) {
                                $budgetItem->setActual($lineTotal, false);
                            }
                        }
                    }
                }
            }
        }

        foreach ($objectLines as $line) {
            $originalData = $unitOfWork->getOriginalEntityData($line);

            if (
                ($this->isValidOriginalData($originalData, 'budget_sub_category_id')) &&
                ($this->isValidOriginalData($originalData, 'budget_main_category_id'))
            ) {
                if (
                    ($originalData['budget_sub_category_id'] && $line->getBudgetSubCategory() == null) ||
                    ($originalData['budget_main_category_id'] && $line->getBudgetMainCategory() == null)
                ) {
                    $this->removeBudgetTotal(
                        $budgetId,
                        $originalData['budget_main_category_id'],
                        $originalData['budget_sub_category_id'],
                        $originalData['lineTotal'],
                        $currency,
                        $unitId
                    );
                } elseif ($line->getBudgetMainCategory() && $line->getBudgetSubCategory()) {
                    $budgetMainCategoryId = $line->getBudgetMainCategory()->getId();
                    $budgetSubCategoryId = $line->getBudgetSubCategory()->getId();
                    $lineTotal = $line->getLineTotal();

                    $budgetItem = $this->getBudgetItem(
                        $budgetId,
                        $budgetMainCategoryId,
                        $budgetSubCategoryId,
                        $currency,
                        $unitId
                    );

                    if ($flag == 'update') {
                        if ($line->getId()) {
                            if (
                                (!$originalData['budget_sub_category_id'] && $line->getBudgetSubCategory()) ||
                                (!$originalData['budget_main_category_id'] && $line->getBudgetMainCategory())
                            ) {
                                if ($budgetItem) {
                                    $budgetItem->setActual($lineTotal, true);
                                }
                            } elseif (
                                ($originalData['budget_sub_category_id'] !== $line->getBudgetSzbCategory()->getId()) ||
                                ($originalData['budget_main_category_id'] !== $line->getBudgetMainCategory()->getId())
                            ) {
                                $this->removeBudgetTotal(
                                    $budgetId,
                                    $originalData['budget_main_category_id'],
                                    $originalData['budget_sub_category_id'],
                                    $originalData['lineTotal'],
                                    $currency,
                                    $unitId
                                );

                                if ($budgetItem) {
                                    $budgetItem->setActual($lineTotal, true);
                                }
                            } else {
                                $currentLineTotal = $this->getCurrentLineTotal($line->getId(), $className);
                                $lineTotalDifference = strval($lineTotal - $currentLineTotal);

                                if ($budgetItem) {
                                    $budgetItem->setActual($lineTotalDifference, true);
                                }
                            }
                        } else {
                            if ($budgetItem) {
                                $budgetItem->setActual($lineTotal, true);
                            }
                        }
                    } elseif ($flag == 'remove') {
                        if ($budgetItem) {
                            $budgetItem->setActual($lineTotal, false);
                        }
                    } else {
                        if ($budgetItem) {
                            $budgetItem->setActual($lineTotal, true);
                        }
                    }
                }
            } else {
                if ($line->getBudgetMainCategory() && $line->getBudgetSubCategory()) {
                    $budgetMainCategoryId = $line->getBudgetMainCategory()->getId();
                    $budgetSubCategoryId = $line->getBudgetSubCategory()->getId();
                    $lineTotal = $line->getLineTotal();

                    $budgetItem = $this->getBudgetItem(
                        $budgetId,
                        $budgetMainCategoryId,
                        $budgetSubCategoryId,
                        $currency,
                        $unitId
                    );

                    if ($budgetItem) {
                        if ($flag == 'remove') {
                            $budgetItem->setActual($lineTotal, false);
                        } else {
                            $budgetItem->setActual($lineTotal, true);
                        }
                    }
                }
            }
        }
    }

    private function removeBudgetTotal(
        string $budgetId,
        string $budgetMainCategoryId,
        string $budgetSubCategoryId,
        string $lineTotal,
        string $currency,
        string $unitId
    ): void {
        $budgetItem = $this->getBudgetItem($budgetId, $budgetMainCategoryId, $budgetSubCategoryId, $currency, $unitId);

        if ($budgetItem) {
            $budgetItem->setActual($lineTotal, false);
        }
    }

    private function getBudgetItem(
        string $budgetId,
        string $budgetMainCategoryId,
        string $budgetSubCategoryId,
        string $currency,
        string $unitId
    ): ?BudgetItem {
        /** @var BudgetItemRepository $budgetItemRepository */
        $budgetItemRepository = $this->entityManager->getRepository(BudgetItem::class);

        return $budgetItemRepository->findOneBy([
            'budget' => $budgetId,
            'budgetMainCategory' => $budgetMainCategoryId,
            'budgetSubCategory' => $budgetSubCategoryId,
            'currency' => $currency,
            'unit' => $unitId
            ])
        ;
    }

    private function getCurrentLineTotal(string $lineId, string $objectClassNameLabel): ?string
    {
        /** @var PurchaseLineRepository $purchaseLineRepository */
        $purchaseLineRepository = $this->entityManager->getRepository(PurchaseLine::class);
        /** @var InvoiceLineRepository $invoiceLineRepository */
        $invoiceLineRepository = $this->entityManager->getRepository(InvoiceLine::class);

        if ($objectClassNameLabel == 'Invoice') {
            $currentLineTotal = $invoiceLineRepository->getCurrentLineTotal($lineId);
        } else {
            $currentLineTotal = $purchaseLineRepository->getCurrentLineTotal($lineId);
        }

        return $currentLineTotal;
    }

    public function isValidOriginalData($originalData, $string): bool
    {
        return is_array($originalData) && array_key_exists($string, $originalData);
    }
}
