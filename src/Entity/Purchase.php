<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
class Purchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $transactionType;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateOfPurchase = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $currency = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $realCurrencyPaid = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $realAmountPaid = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateTimeAdded = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateTimeEdited = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    private ?Account $account = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    private ?Budget $budget = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    private ?Department $department = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    private ?Supplier $supplier = null;

    #[ORM\ManyToOne(inversedBy: 'addedPurchases')]
    private ?User $addedByUser = null;

    #[ORM\ManyToOne(inversedBy: 'editedPurchases')]
    private ?User $editedByUser = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $addedByUserDeleted = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $editedByUserDeleted = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    private ?Unit $unit = null;

    #[ORM\OneToMany(targetEntity: PurchaseLine::class, mappedBy: 'purchase')]
    private Collection $purchaseLines;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'purchase')]
    private Collection $transactions;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'purchase')]
    private Collection $comments;

    public function __construct()
    {
        $this->purchaseLines = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString()
    {
        if ($this->account) {
            return $this->account . ' - ' . 'Purchase';
        } else {
            return 'New Purchase';
        }
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

    public function getDateOfPurchase(): ?\DateTimeInterface
    {
        return $this->dateOfPurchase;
    }

    public function setDateOfPurchase(\DateTimeInterface $dateOfPurchase): static
    {
        $this->dateOfPurchase = $dateOfPurchase;

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

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): static
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

    public function setRealAmountPaid(?string $realAmountPaid): static
    {
        $this->realAmountPaid = $realAmountPaid;

        return $this;
    }

    public function getDateTimeAdded(): ?\DateTimeInterface
    {
        return $this->dateTimeAdded;
    }

    public function setDateTimeAdded(?\DateTimeInterface $dateTimeAdded): static
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
     * @return Collection<int, PurchaseLine>
     */
    public function getPurchaseLines(): Collection
    {
        return $this->purchaseLines;
    }

    public function addPurchaseLine(PurchaseLine $purchaseLine): static
    {
        if (!$this->purchaseLines->contains($purchaseLine)) {
            $this->purchaseLines->add($purchaseLine);
            $purchaseLine->setPurchase($this);
        }

        return $this;
    }

    public function removePurchaseLine(PurchaseLine $purchaseLine): static
    {
        if ($this->purchaseLines->removeElement($purchaseLine)) {
            // set the owning side to null (unless already changed)
            if ($purchaseLine->getPurchase() === $this) {
                $purchaseLine->setPurchase(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setPurchase($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getPurchase() === $this) {
                $transaction->setPurchase(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPurchase($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPurchase() === $this) {
                $comment->setPurchase(null);
            }
        }

        return $this;
    }
}
