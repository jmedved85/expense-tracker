<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class NotNegative extends Constraint
{
    public string $message = 'The value "{{ value }}" should be numeric and a positive number.';
}
