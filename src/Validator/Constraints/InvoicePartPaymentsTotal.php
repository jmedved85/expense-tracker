<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class InvoicePartPaymentsTotal extends Constraint
{
    public string $message = 'The total of Part-Payments cannot exceed the Invoice amount.';
}
