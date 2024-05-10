<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;

class CustomButtonType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'custom_button';
    }
}
