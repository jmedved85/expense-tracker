<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class NoInvoiceLineAdded extends Constraint
{
    public string $message = 'No Invoice Line is added, please add at least one Invoice Line.';
}
