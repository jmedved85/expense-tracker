<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class LineNetTotalDifference extends Constraint
{
    public string $message = "%lineType% Line error for '%lineDescription%': 
        'NET Value' and 'Line Total' values are not equal when 'VAT' and 'VAT Value' is left blank. 
        Please ensure that entered 'NET Value' and 'Line Total' values are equal."
    ;
}
