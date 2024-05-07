<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;

class AppExtension extends AbstractExtension
{
    public function __constructor()
    {
    }

    public function getFilters()
    {
        return [];
    }

    public function getFunctions()
    {
        return [];
    }
}