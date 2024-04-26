<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class NoPurchaseLineAdded extends Constraint
{
    public string $message = 'No Purchase Line is added, please add at least one Purchase Line.';
}
