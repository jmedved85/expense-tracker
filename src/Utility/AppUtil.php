<?php

namespace App\Utility;

use App\Entity\Unit;
use DateTimeInterface;
use Twig\Environment;

class AppUtil
{
    public function __construct(
        private Environment $twig,
        private string $projectDir
    ) {
    }

    /**
     * Get the default header HTML for documents.
     */
    public function getHeaderHtml(?Unit $unit = null, ?DateTimeInterface $recordDate = null): string
    {
        return $this->twig->render('PDF/header.html.twig', [
            'unit' => $unit,
            'projectDir' => $this->projectDir,
            'recordDate' => $recordDate,
        ]);
    }

    /**
     * Get the default footer HTML for documents.
     */
    public function getFooterHtml(?Unit $unit = null, bool $currentTime = true): string
    {
        return $this->twig->render('PDF/footer.html.twig', [
            'unit' => $unit,
            'currentTime' => $currentTime,
            'projectDir' => $this->projectDir
        ]);
    }
}
