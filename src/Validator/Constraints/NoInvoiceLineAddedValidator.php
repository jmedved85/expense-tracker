<?php

namespace App\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NoInvoiceLineAddedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if ($value instanceof PersistentCollection || $value instanceof ArrayCollection) {
            if (!$constraint instanceof NoInvoiceLineAdded) {
                throw new UnexpectedTypeException($constraint, NoInvoiceLineAdded::class);
            }

            if (!$value || $value === '') {
                return;
            }

            if ($value->isEmpty()) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation()
                ;
            }
        }
    }
}
