<?php

namespace App\Validator\Constraints;

use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NotNegativeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!($value instanceof PersistentCollection)) {
            if (!$constraint instanceof NotNegative) {
                throw new UnexpectedTypeException($constraint, NotNegative::class);
            }

            if (!$value || $value === '') {
                return;
            }

            if ($value <= 0 || !is_numeric($value)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $value)
                    ->addViolation()
                ;
            }
        }
    }
}
