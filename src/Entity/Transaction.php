<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $transactionNumber = null;

    #[ORM\Column]
    private ?int $transactionType = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateTimeAdded = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateTimeEdited = null;

    #[ORM\Column(length: 3)]
    private ?string $currency = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $toCurrency = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $newValue = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $amountFromAccount = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $realCurrencyPaid = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $realAmountPaid = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $moneyReturnedDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $moneyReturnedAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $moneyIn = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $moneyOut = null;

    #[ORM\Column(nullable: true)]
    private ?bool $bankFeeAdded = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $bankFeeCurrency = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $bankFeeAmount = null;

    #[ORM\Column(nullable: true)]
    private ?bool $bankFeeNotApplicable = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $balanceMainAccount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $balanceTransferFromAccount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $balanceTransferToAccount = null;

    #[ORM\ManyToOne(inversedBy: 'mainAccountTransactions')]
    private ?Account $mainAccount = null;

    #[ORM\ManyToOne(inversedBy: 'transferFromAccountTransactions')]
    private ?Account $transferFromAccount = null;

    #[ORM\ManyToOne(inversedBy: 'transferToAccountTransactions')]
    private ?Account $transferToAccount = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    private ?Invoice $invoice = null;

    #[ORM\OneToOne(targetEntity: InvoicePartPayment::class, mappedBy: "transaction", cascade: ["persist", "remove"])]
    private ?InvoicePartPayment $invoicePartPayment = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    private ?Purchase $purchase = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'transactions')]
    private ?self $transaction = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'transaction')]
    private Collection $transactions;

    #[ORM\ManyToOne(inversedBy: 'addedTransactions')]
    private ?User $addedByUser = null;

    #[ORM\ManyToOne(inversedBy: 'editedTransactions')]
    private ?User $editedByUser = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $addedByUserDeleted = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $editedByUserDeleted = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    private ?Unit $unit = null;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function __toString()
    {
        $transactionType = $this->getTransactionType();

        if ($transactionType::FROM_ACCOUNT || $transactionType::TO_ACCOUNT) {
            return $this->getTransferFromAccount()->getNameWithCurrency()
            . ' to ' . $this->getTransferToAccount()->getNameWithCurrency()
            . ' - Transfer';
        } elseif (
            $transactionType::CURRENCY_EXCHANGE
            && $this->getTransferFromAccount()
            && $this->getTransferToAccount()
        ) {
            return $this->getTransferFromAccount()->getNameWithCurrency()
            . ' to ' . $this->getTransferToAccount()->getNameWithCurrency()
            . ' - Currency exchange';
        } else {
            return $this->getTransactionTypeName() ? $this->getTransactionTypeName() : '';
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransactionNumber(): ?int
    {
        return $this->transactionNumber;
    }

    public function getTransactionNumberString(): ?string
    {
        $transactionNumber = $this->transactionNumber;

        if ($transactionNumber > 9999) {
            $transactionNumberString = str_pad($transactionNumber, 6, "0", STR_PAD_LEFT);

            return $transactionNumberString;
        } else {
            $transactionNumberString = str_pad($transactionNumber, 4, "0", STR_PAD_LEFT);

            return $transactionNumberString;
        }
    }

    public function setTransactionNumber(int $transactionNumber): static
    {
        $this->transactionNumber = $transactionNumber;

        return $this;
    }

    public function getTransactionType(): TransactionType
    {
        return TransactionType::from($this->transactionType);
    }

    public function getTransactionTypeName(): string
    {
        return TransactionType::getName($this->transactionType);
    }

    public function setTransactionType(TransactionType $transactionType): self
    {
        $this->transactionType = $transactionType->value;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

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

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getToCurrency(): ?string
    {
        return $this->toCurrency;
    }

    public function setToCurrency(?string $toCurrency): static
    {
        $this->toCurrency = $toCurrency;

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

    public function getNewValue(): ?string
    {
        return $this->newValue;
    }

    public function setNewValue(?string $newValue): static
    {
        $this->newValue = $newValue;

        return $this;
    }

    public function getAmountFromAccount(): ?string
    {
        return $this->amountFromAccount;
    }

    public function setAmountFromAccount(?string $amountFromAccount): static
    {
        $this->amountFromAccount = $amountFromAccount;

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

    public function getMoneyReturnedDate(): ?\DateTimeInterface
    {
        return $this->moneyReturnedDate;
    }

    public function setMoneyReturnedDate(?\DateTimeInterface $moneyReturnedDate): static
    {
        $this->moneyReturnedDate = $moneyReturnedDate;

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

    public function getMoneyIn(): ?string
    {
        return $this->moneyIn;
    }

    public function setMoneyIn(?string $moneyIn): static
    {
        $this->moneyIn = $moneyIn;

        return $this;
    }

    public function getMoneyOut(): ?string
    {
        return $this->moneyOut;
    }

    public function setMoneyOut(?string $moneyOut): static
    {
        $this->moneyOut = $moneyOut;

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

    public function getBankFeeCurrency(): ?string
    {
        return $this->bankFeeCurrency;
    }

    public function setBankFeeCurrency(?string $bankFeeCurrency): static
    {
        $this->bankFeeCurrency = $bankFeeCurrency;

        return $this;
    }

    public function getBankFeeAmount(): ?string
    {
        return $this->bankFeeAmount;
    }

    public function setBankFeeAmount(?string $amount, bool $increase = null): static
    {
        $bankFeeAmount = $this->bankFeeAmount;

        if ($amount !== null) {
            $amount = floatval($amount);
            $bankFeeAmount = $bankFeeAmount !== null ? floatval($bankFeeAmount) : 0;

            if ($increase) {
                $bankFeeAmount += $amount;
            } elseif ($increase === false) {
                $bankFeeAmount -= $amount;
            } else {
                $bankFeeAmount = $amount;
            }
        }

        $this->bankFeeAmount = $bankFeeAmount;

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

    public function getBalanceMainAccount(): ?string
    {
        return $this->balanceMainAccount;
    }

    public function setBalanceMainAccount(?string $amount, bool $increase = null): static
    {
        $currentBalance = floatval($this->balanceMainAccount);

        if ($increase) {
            $currentBalance += floatval($amount);
        } elseif ($increase === false) {
            $currentBalance -= floatval($amount);
        } else {
            $currentBalance = floatval($amount);
        }

        $this->balanceMainAccount = strval($currentBalance);

        return $this;
    }

    public function getBalanceTransferFromAccount(): ?string
    {
        return $this->balanceTransferFromAccount;
    }

    public function setBalanceTransferFromAccount(?string $amount, bool $increase = null): static
    {
        $currentBalance = floatval($this->balanceTransferFromAccount);

        if ($increase) {
            $currentBalance += floatval($amount);
        } elseif ($increase === false) {
            $currentBalance -= floatval($amount);
        } else {
            $currentBalance = floatval($amount);
        }

        $this->balanceTransferFromAccount = strval($currentBalance);

        return $this;
    }

    public function getBalanceTransferToAccount(): ?string
    {
        return $this->balanceTransferToAccount;
    }

    public function setBalanceTransferToAccount(?string $amount, bool $increase = null): static
    {
        $currentBalance = floatval($this->balanceTransferToAccount);

        if ($increase) {
            $currentBalance += floatval($amount);
        } elseif ($increase === false) {
            $currentBalance -= floatval($amount);
        } else {
            $currentBalance = floatval($amount);
        }

        $this->balanceTransferToAccount = strval($currentBalance);

        return $this;
    }

    public function recalculateBalance(?string $amount, bool $increase = null): float
    {
        $currentBalance = floatval($this->balanceMainAccount);

        if ($increase) {
            $currentBalance += floatval($amount);
        } elseif ($increase === false) {
            $currentBalance -= floatval($amount);
        } else {
            $currentBalance = floatval($amount);
        }

        return $currentBalance;
    }

    public function getMainAccount(): ?Account
    {
        return $this->mainAccount;
    }

    public function setMainAccount(?Account $mainAccount): static
    {
        $this->mainAccount = $mainAccount;

        return $this;
    }

    public function getTransferFromAccount(): ?Account
    {
        return $this->transferFromAccount;
    }

    public function setTransferFromAccount(?Account $transferFromAccount): static
    {
        $this->transferFromAccount = $transferFromAccount;

        return $this;
    }

    public function getTransferToAccount(): ?Account
    {
        return $this->transferToAccount;
    }

    public function setTransferToAccount(?Account $transferToAccount): static
    {
        $this->transferToAccount = $transferToAccount;

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

    public function getInvoicePartPayment(): ?InvoicePartPayment
    {
        return $this->invoicePartPayment;
    }
    
    public function setInvoicePartPayment(?InvoicePartPayment $invoicePartPayment): self
    {
        // unset the owning side of the relation if necessary
        if ($invoicePartPayment === null && $this->invoicePartPayment !== null) {
            $this->invoicePartPayment->setTransaction(null);
        }
    
        // set the owning side of the relation if necessary
        if ($invoicePartPayment !== null && $invoicePartPayment->getTransaction() !== $this) {
            $invoicePartPayment->setTransaction($this);
        }
    
        $this->invoicePartPayment = $invoicePartPayment;
    
        return $this;
    }

    public function getPurchase(): ?Purchase
    {
        return $this->purchase;
    }

    public function setPurchase(?Purchase $purchase): static
    {
        $this->purchase = $purchase;

        return $this;
    }

    public function getTransaction(): ?self
    {
        return $this->transaction;
    }

    public function setTransaction(?self $transaction): static
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(self $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setTransaction($this);
        }

        return $this;
    }

    public function removeTransaction(self $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getTransaction() === $this) {
                $transaction->setTransaction(null);
            }
        }

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
}
