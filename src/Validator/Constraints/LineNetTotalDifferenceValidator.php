<?php

namespace App\Validator\Constraints;

use App\Entity\InvoiceLine;
use App\Entity\PurchaseLine;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LineNetTotalDifferenceValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if ($value instanceof PersistentCollection || $value instanceof ArrayCollection) {
            if (!$constraint instanceof LineNetTotalDifference) {
                throw new UnexpectedTypeException($constraint, LineNetTotalDifference::class);
            }

            $violation = false;
            $lineType = '';
            $lineDescription = '';

            if (!($value->isEmpty())) {
                $lines = $value->toArray();

                if (!empty($lines)) {
                    foreach ($lines as $line) {
                        $netValue = floatval($line->getNetValue());
                        $vat = floatval($line->getVat());
                        $vatValue = floatval($line->getVatValue());
                        $lineTotal = floatval($line->getLineTotal());
                        $lineDescription = $line->getDescription();

                        // if ($vat == 0 && $vatValue == 0) {
                        //     if ($netValue !== $lineTotal) {
                        //         if ($line instanceof InvoiceLine) {
                        //             $lineType = 'Invoice';
                        //         } else if ($line instanceof PurchaseLine) {
                        //             $lineType = 'Purchase';
                        //         }

                        //         $violation = true;

                        //         break;
                        //     }
                        // }
                    }
                } else {
                    return;
                }
            }

            if ($violation) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%lineType%', $lineType)
                    ->setParameter('%lineDescription%', $lineDescription)
                    ->addViolation()
                ;
            }
        }
    }
}
