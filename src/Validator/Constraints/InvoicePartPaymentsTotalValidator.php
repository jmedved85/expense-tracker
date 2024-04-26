<?php

namespace App\Validator\Constraints;

use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class InvoicePartPaymentsTotalValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if ($value instanceof PersistentCollection) {
            $partPayments = $value->toArray();
            $invoiceAmount = $value->getOwner()->getAmount();

            $totalPartPayments = null;

            foreach ($partPayments as $partPayment) {
                $totalPartPayments += $partPayment->getAmount();
            }

            if ($totalPartPayments > $invoiceAmount) {
                if (isset($constraint->message)) {
                    $this->context->buildViolation($constraint->message)
                        ->addViolation();
                }
            }
        }
    }
}
