<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datetime = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?User $addedByUser = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $addedByUserDeleted = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Invoice $invoice = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Purchase $purchase = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Supplier $supplier = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?BudgetItem $budgetItem = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Unit $unit = null;

    public function __toString()
    {
        return (string) $this->getUserDateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatetime(): ?\DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTimeInterface $datetime): static
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getAddedByUser(): ?User
    {
        return $this->addedByUser;
    }

    public function setAddedByUser(?User $addedByUser): static
    {
        $this->addedByUser = $addedByUser;

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

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        $this->invoice = $invoice;

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

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getBudgetItem(): ?BudgetItem
    {
        return $this->budgetItem;
    }

    public function setBudgetItem(?BudgetItem $budgetItem): static
    {
        $this->budgetItem = $budgetItem;

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

    /* Get formatted date/time */
    public function getDateTimeText(): ?string
    {
        $dateTime = $this->getDateTime();
        $dateTimeText = $dateTime ? $dateTime->format('d/m/Y H:i') : null;

        return $dateTimeText;
    }

    /* Get user/datetime combined */
    public function getUserDateTime(): ?string
    {
        $dateTime = $this->getDateTimeText();

        if ($this->addedByUser) {
            $userName = $this->addedByUser->getUsername();

            return $userName . ' (' . $dateTime . ')';
        } elseif ($this->addedByUserDeleted) {
            return $this->addedByUserDeleted . ' (' . $dateTime . ')';
        } else {
            return null;
        }
    }

    public function getDateTimeUser(): ?string
    {
        $user = $this->getAddedByUser();
        $dateTime = $this->getDateTimeText();
        $dateTimeUser = $user && $dateTime ? $dateTime . ' - ' . $user : null;

        return $dateTimeUser;
    }
}
