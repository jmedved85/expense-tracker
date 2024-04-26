<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class PartPaymentValue extends Constraint
{
    public string $message = 'The value "{{ value }}" should be numeric and a not negative number.';
}
