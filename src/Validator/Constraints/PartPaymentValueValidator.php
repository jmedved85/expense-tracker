<?php

namespace App\Validator\Constraints;

use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PartPaymentValueValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if ($value instanceof PersistentCollection) {
            if (!$constraint instanceof PartPaymentValue) {
                throw new UnexpectedTypeException($constraint, PartPaymentValue::class);
            }

            if (!$value || $value === '') {
                return;
            }

            $partPayments = $value->toArray();

            foreach ($partPayments as $partPayment) {
                if ($partPayment->getAmount() !== null && $partPayment->getAmount() !== '0') {
                    if ($partPayment->getAmount() < 0 || !is_numeric($partPayment->getAmount())) {
                        $this->context->buildViolation($constraint->message)
                            ->setParameter('{{ value }}', $partPayment->getAmount())
                            ->addViolation()
                        ;
                    }
                } else {
                    $this->context->buildViolation('The value cannot be zero or blank.')
                        ->addViolation()
                    ;
                }

                /* Bank Fee can be also null to delete it from the Part payment */
                if (
                    $partPayment->getBankFeeAmount() < 0
                    || (!is_numeric($partPayment->getBankFeeAmount())
                    && !is_null($partPayment->getBankFeeAmount())
                    )
                ) {
                    $this->context->buildViolation($constraint->message)
                        ->setParameter('{{ value }}', $partPayment->getBankFeeAmount())
                        ->addViolation()
                    ;
                }
            }
        }
    }
}
