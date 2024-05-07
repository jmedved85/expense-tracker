<?php

namespace App\Entity;

use App\Repository\InvoiceLineRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceLineRepository::class)]
class InvoiceLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2, nullable: true)]
    private ?string $vat = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $vatValue = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $netValue = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2)]
    private ?string $lineTotal = null;

    #[ORM\ManyToOne(inversedBy: 'invoiceLines')]
    private ?Invoice $invoice = null;

    #[ORM\ManyToOne(inversedBy: 'invoiceLines')]
    private ?BudgetSubCategory $budgetSubCategory = null;

    #[ORM\ManyToOne(inversedBy: 'invoiceLines')]
    private ?GeneralCategory $generalCategory = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getVat(): ?string
    {
        return $this->vat;
    }

    public function setVat(?string $vat): static
    {
        $this->vat = $vat;

        return $this;
    }

    public function getVatValue(): ?string
    {
        return $this->vatValue;
    }

    public function setVatValue(?string $vatValue): static
    {
        $this->vatValue = $vatValue;

        return $this;
    }

    public function getNetValue(): ?string
    {
        return $this->netValue;
    }

    public function setNetValue(?string $netValue): static
    {
        $this->netValue = $netValue;

        return $this;
    }

    public function getLineTotal(): ?string
    {
        return $this->lineTotal;
    }

    public function setLineTotal(string $lineTotal): static
    {
        $this->lineTotal = $lineTotal;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getBudgetSubCategory(): ?BudgetSubCategory
    {
        return $this->budgetSubCategory;
    }

    public function setBudgetSubCategory(?BudgetSubCategory $budgetSubCategory): static
    {
        $this->budgetSubCategory = $budgetSubCategory;

        return $this;
    }

    public function getGeneralCategory(): ?GeneralCategory
    {
        return $this->generalCategory;
    }

    public function setGeneralCategory(?GeneralCategory $generalCategory): static
    {
        $this->generalCategory = $generalCategory;

        return $this;
    }
}
