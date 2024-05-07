<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $invoiceNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $invoiceDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $invoiceDateDue = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $invoiceDatePaid = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $priority = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $approvalStatus = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $paymentStatus = null;

    #[ORM\Column(length: 3)]
    private ?string $currency = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $realCurrencyPaid = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $realAmountPaid = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $restPaymentTotal = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $totalPaid = null;

    #[ORM\Column(nullable: true)]
    private ?bool $bankFeeAdded = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $bankFeeAmount = null;

    #[ORM\Column(nullable: true)]
    private ?bool $bankFeeNotApplicable = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateTimeAdded = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateTimeEdited = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Account $account = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Budget $budget = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Department $department = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Supplier $supplier = null;

    #[ORM\ManyToOne(inversedBy: 'addedInvoices')]
    private ?User $addedByUser = null;

    #[ORM\ManyToOne(inversedBy: 'editedInvoices')]
    private ?User $editedByUser = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $addedByUserDeleted = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $editedByUserDeleted = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Unit $unit = null;

    /**
     * @var Collection<int, InvoiceLine>
     */
    #[ORM\OneToMany(targetEntity: InvoiceLine::class, mappedBy: 'invoice')]
    private Collection $invoiceLines;

    /**
     * @var Collection<int, InvoicePartPayment>
     */
    #[ORM\OneToMany(targetEntity: InvoicePartPayment::class, mappedBy: 'invoice')]
    private Collection $invoicePartPayments;

    public function __construct()
    {
        $this->invoiceLines = new ArrayCollection();
        $this->invoicePartPayments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): static
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getInvoiceDate(): ?\DateTimeInterface
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(?\DateTimeInterface $invoiceDate): static
    {
        $this->invoiceDate = $invoiceDate;

        return $this;
    }

    public function getInvoiceDateDue(): ?\DateTimeInterface
    {
        return $this->invoiceDateDue;
    }

    public function setInvoiceDateDue(?\DateTimeInterface $invoiceDateDue): static
    {
        $this->invoiceDateDue = $invoiceDateDue;

        return $this;
    }

    public function getInvoiceDatePaid(): ?\DateTimeInterface
    {
        return $this->invoiceDatePaid;
    }

    public function setInvoiceDatePaid(?\DateTimeInterface $invoiceDatePaid): static
    {
        $this->invoiceDatePaid = $invoiceDatePaid;

        return $this;
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

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(?string $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getApprovalStatus(): ?string
    {
        return $this->approvalStatus;
    }

    public function setApprovalStatus(?string $approvalStatus): static
    {
        $this->approvalStatus = $approvalStatus;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?string $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;

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

    public function setRealAmountPaid(?string $amount, bool $increase = null): static
    {
        $realAmountPaid = floatval($this->getRealAmountPaid());
        
        if ($increase) {
            $realAmountPaid += floatval($amount);
        } else if ($increase === false) {
            $realAmountPaid -= floatval($amount);
        } else {
            $realAmountPaid = floatval($amount);
        }
        
        $this->realAmountPaid = strval($realAmountPaid);
        
        return $this;
    }

    public function getRestPaymentTotal(): ?string
    {
        return $this->restPaymentTotal;
    }

    public function setRestPaymentTotal(?string $amount, bool $increase = null): static
    {
        $restPaymentTotal = floatval($this->getRestPaymentTotal());

        if ($increase) {
            $restPaymentTotal += floatval($amount);
        } else if ($increase === false) {
            $restPaymentTotal -= floatval($amount);
        } else {
            $restPaymentTotal = floatval($amount);
        }
        
        $this->restPaymentTotal = strval($restPaymentTotal);

        return $this;
    }

    public function getTotalPaid(): ?string
    {
        if ($this->totalPaid == null) {
            return number_format($this->totalPaid, 2);
        } else {
            return $this->totalPaid;
        }
    }

    public function setTotalPaid(?string $amount, bool $increase = null): static
    {
        $totalPaid = floatval($this->getTotalPaid());

        if ($increase) {
            $totalPaid += floatval($amount);
        } else if ($increase === false) {
            $totalPaid -= floatval($amount);
        } else {
            $totalPaid = floatval($amount);
        }
        
        $this->totalPaid = strval($totalPaid);

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

    public function setBankFeeAmount(?string $amount, bool $increase = null): static
    {
        if ($amount == null) {
            if ($this->getPaymentStatus() !== 'Part-Paid') {
                $amount = floatval(0);
            }
        }

        $bankFeeAmount = $this->bankFeeAmount;

        if ($increase) {
            $bankFeeAmount += $amount;
        } else if ($increase === false) {
            $bankFeeAmount -= $amount;
        } else if ($amount !== null) {
            $bankFeeAmount = $amount;
        }
        
        $this->bankFeeAmount = $bankFeeAmount;

        if ($this->paymentStatus == 'Unpaid') {
            $this->setBankFeeAdded(false);
        } else if ($this->paymentStatus == 'Part-Paid') {
            $this->setBankFeeAdded(false);
        } else {
            $invoicePartPayments = $this->invoicePartPayments->toArray();

            $allPartPaymentsBankFeesPaid = true;

            foreach ($invoicePartPayments as $partPayment) {
                if ($partPayment->getTransaction()->isBankFeeAdded() == false) {
                    $allPartPaymentsBankFeesPaid = false;
                    break;
                }
            }

            if (!$allPartPaymentsBankFeesPaid) {
                $this->setBankFeeAdded(false);
            } else {
                if ($this->bankFeeAmount > 0) {
                    $this->setBankFeeAdded(true);
                } else {
                    $this->setBankFeeAdded(false);
                }
            }
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

    public function getDateTimeAdded(): ?\DateTimeInterface
    {
        return $this->dateTimeAdded;
    }

    public function setDateTimeAdded(\DateTimeInterface $dateTimeAdded): static
    {
        $this->dateTimeAdded = $dateTimeAdded;

        return $this;
    }

    public function getDateTimeEdited(): ?\DateTimeInterface
    {
        return $this->dateTimeEdited;
    }

    public function setDateTimeEdited(?\DateTimeInterface $dateTimeEdited): static
    {
        $this->dateTimeEdited = $dateTimeEdited;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): static
    {
        $this->account = $account;

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): static
    {
        $this->budget = $budget;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): static
    {
        $this->department = $department;

        return $this;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getAddedByUser(): ?User
    {
        return $this->addedByUser;
    }

    public function getAddedByUserDateTime(): ?string
    {
        $dateTimeAdded = $this->getDateTimeAdded() ? $this->getDateTimeAdded()->format('d/m/Y H:i:s') : '';

        if ($this->addedByUser) {
            $userName = '';

            if ($this->addedByUser->getUsername()) {
                $userName = $this->addedByUser->getUsername();
            } else {
                $userName = $this->addedByUser->getEmail();
            }

            return $userName . ' (' . $dateTimeAdded . ')';
        } elseif ($this->addedByUserDeleted) {
            return $this->addedByUserDeleted . ' (' . $dateTimeAdded . ')';
        } else {
            return null;
        }
    }

    public function setAddedByUser(?User $addedByUser): static
    {
        $this->addedByUser = $addedByUser;

        return $this;
    }

    public function getEditedByUser(): ?User
    {
        return $this->editedByUser;
    }

    public function getEditedByUserDateTime(): ?string
    {
        $dateTimeAdded = $this->getDateTimeAdded() ? $this->getDateTimeAdded()->format('d/m/Y H:i:s') : '';

        if ($this->editedByUser) {
            $userName = '';

            if ($this->editedByUser->getUsername()) {
                $userName = $this->editedByUser->getUsername();
            } else {
                $userName = $this->editedByUser->getEmail();
            }

            return $userName . ' (' . $dateTimeAdded . ')';
        } elseif ($this->editedByUserDeleted) {
            return $this->editedByUserDeleted . ' (' . $dateTimeAdded . ')';
        } else {
            return null;
        }
    }

    public function setEditedByUser(?User $editedByUser): static
    {
        $this->editedByUser = $editedByUser;

        return $this;
    }

    public function getAddedByUserDeleted(): ?string
    {
        return $this->addedByUserDeleted;
    }

    public function setAddedByUserDeleted(?string $addedByUserDeleted): static
    {
        $this->addedByUserDeleted = $addedByUserDeleted;

        return $this;
    }

    public function getEditedByUserDeleted(): ?string
    {
        return $this->editedByUserDeleted;
    }

    public function setEditedByUserDeleted(?string $editedByUserDeleted): static
    {
        $this->editedByUserDeleted = $editedByUserDeleted;

        return $this;
    }

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return Collection<int, InvoiceLine>
     */
    public function getInvoiceLines(): Collection
    {
        return $this->invoiceLines;
    }

    public function addInvoiceLine(InvoiceLine $invoiceLine): static
    {
        if (!$this->invoiceLines->contains($invoiceLine)) {
            $this->invoiceLines->add($invoiceLine);
            $invoiceLine->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceLine(InvoiceLine $invoiceLine): static
    {
        if ($this->invoiceLines->removeElement($invoiceLine)) {
            // set the owning side to null (unless already changed)
            if ($invoiceLine->getInvoice() === $this) {
                $invoiceLine->setInvoice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InvoicePartPayment>
     */
    public function getInvoicePartPayments(): Collection
    {
        return $this->invoicePartPayments;
    }

    public function addInvoicePartPayment(InvoicePartPayment $invoicePartPayment): static
    {
        if (!$this->invoicePartPayments->contains($invoicePartPayment)) {
            $this->invoicePartPayments->add($invoicePartPayment);
            $invoicePartPayment->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoicePartPayment(InvoicePartPayment $invoicePartPayment): static
    {
        if ($this->invoicePartPayments->removeElement($invoicePartPayment)) {
            // set the owning side to null (unless already changed)
            if ($invoicePartPayment->getInvoice() === $this) {
                $invoicePartPayment->setInvoice(null);
            }
        }

        return $this;
    }
}
