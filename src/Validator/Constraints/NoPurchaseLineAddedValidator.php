<?php

namespace App\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NoPurchaseLineAddedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if ($value instanceof PersistentCollection || $value instanceof ArrayCollection) {
            if (!$constraint instanceof NoPurchaseLineAdded) {
                throw new UnexpectedTypeException($constraint, NoPurchaseLineAdded::class);
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
