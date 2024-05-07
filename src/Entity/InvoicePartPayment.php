<?php

namespace App\Entity;

use App\Repository\InvoicePartPaymentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoicePartPaymentRepository::class)]
class InvoicePartPayment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datePaid = null;

    #[ORM\Column(length: 3)]
    private ?string $currency = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $restPaymentAmount = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $realCurrencyPaid = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $realAmountPaid = null;

    #[ORM\Column(nullable: true)]
    private ?bool $bankFeeAdded = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $bankFeeAmount = null;

    #[ORM\Column(nullable: true)]
    private ?bool $bankFeeNotApplicable = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $moneyReturnedAmount = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $moneyReturnedDate = null;

    #[ORM\ManyToOne(inversedBy: 'invoicePartPayments')]
    private ?Invoice $invoice = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Transaction $transaction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatePaid(): ?\DateTimeInterface
    {
        return $this->datePaid;
    }

    public function setDatePaid(\DateTimeInterface $datePaid): static
    {
        $this->datePaid = $datePaid;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getRestPaymentAmount(): ?string
    {
        return $this->restPaymentAmount;
    }

    public function setRestPaymentAmount(?string $amount, bool $increase = null): static
    {
        $currentRestPayment = floatval($this->restPaymentAmount);

        if ($increase) {
            $currentRestPayment += floatval($amount);
        } elseif ($increase === false) {
            $currentRestPayment -= floatval($amount);
        } else {
            $currentRestPayment = floatval($amount);
        }

        $this->restPaymentAmount = strval($currentRestPayment);

        return $this;
    }

    public function getRealCurrencyPaid(): ?string
    {
        return $this->realCurrencyPaid;
    }

    public function setRealCurrencyPaid(?string $realCurrencyPaid): static
    {
        $this->realCurrencyPaid = $realCurrencyPaid;

        return $this;
    }

    public function getRealAmountPaid(): ?string
    {
        return $this->realAmountPaid;
    }

    public function setRealAmountPaid(?string $realAmountPaid): static
    {
        $this->realAmountPaid = $realAmountPaid;

        return $this;
    }

    public function isBankFeeAdded(): ?bool
    {
        return $this->bankFeeAdded;
    }

    public function setBankFeeAdded(?bool $bankFeeAdded): static
    {
        $this->bankFeeAdded = $bankFeeAdded;

        return $this;
    }

    public function getBankFeeAmount(): ?string
    {
        return $this->bankFeeAmount;
    }

    public function setBankFeeAmount(?string $bankFeeAmount): static
    {
        $this->bankFeeAmount = $bankFeeAmount;

        if ($this->bankFeeAmount > 0) {
            $this->setBankFeeAdded(true);
        } else {
            $this->setBankFeeAdded(false);
        }

        return $this;
    }

    public function isBankFeeNotApplicable(): ?bool
    {
        return $this->bankFeeNotApplicable;
    }

    public function setBankFeeNotApplicable(?bool $bankFeeNotApplicable): static
    {
        $this->bankFeeNotApplicable = $bankFeeNotApplicable;

        return $this;
    }

    public function getMoneyReturnedAmount(): ?string
    {
        return $this->moneyReturnedAmount;
    }

    public function setMoneyReturnedAmount(?string $moneyReturnedAmount): static
    {
        $this->moneyReturnedAmount = $moneyReturnedAmount;

        return $this;
    }

    public function getMoneyReturnedDate(): ?\DateTimeInterface
    {
        return $this->moneyReturnedDate;
    }

    public function setMoneyReturnedDate(?\DateTimeInterface $moneyReturnedDate): static
    {
        $this->moneyReturnedDate = $moneyReturnedDate;

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

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): static
    {
        // unset the owning side of the relation if necessary
        if ($transaction === null && $this->transaction !== null) {
            $this->transaction->setInvoicePartPayment(null);
        }

        // set the owning side of the relation if necessary
        if ($transaction !== null && $transaction->getInvoicePartPayment() !== $this) {
            $transaction->setInvoicePartPayment($this);
        }

        $this->transaction = $transaction;

        return $this;
    }
}
