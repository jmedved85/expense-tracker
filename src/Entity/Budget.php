<?php

namespace App\Entity;

use App\Repository\BudgetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BudgetRepository::class)]
class Budget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $budgetType;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $currency = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $totalBudgeted = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $totalActual = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $leftOver = null;

    #[ORM\ManyToOne(inversedBy: 'addedBudgets')]
    private ?User $addedByUser = null;

    #[ORM\ManyToOne(inversedBy: 'editedBudgets')]
    private ?User $editedByUser = null;

    #[ORM\OneToMany(targetEntity: BudgetItem::class, mappedBy: 'budget')]
    private Collection $budgetItems;

    #[ORM\ManyToOne(inversedBy: 'budgets')]
    private ?Unit $unit = null;

    #[ORM\OneToMany(targetEntity: Purchase::class, mappedBy: 'budget')]
    private Collection $purchases;

    public function __construct()
    {
        $this->budgetItems = new ArrayCollection();
        $this->purchases = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBudgetType(): BudgetType
    {
        return BudgetType::from($this->budgetType);
    }

    public function getBudgetTypeName(): string
    {
        return BudgetType::getName($this->budgetType);
    }

    public function setBudgetType(BudgetType $budgetType): self
    {
        $this->budgetType = $budgetType->value;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        $startDate = $this->getStartDate();

        $startDateMutable = new \DateTime($startDate->format('Y-m-d H:i:s'));

        $endDate = match ($this->getBudgetType()) {
            BudgetType::MONTHLY => $startDateMutable->modify('+1 month'),
            BudgetType::ANNUAL => $startDateMutable->modify('+1 year'),
            BudgetType::QUARTERLY => $startDateMutable->modify('+3 months'),
            BudgetType::BI_ANNUAL => $startDateMutable->modify('+6 months'),
            BudgetType::WEEKLY => $startDateMutable->modify('+1 week'),

            default => $startDateMutable,
        };

        return $endDate;
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

    public function getTotalBudgeted(): ?string
    {
        return $this->totalBudgeted;
    }

    public function setTotalBudgeted(?string $totalBudgeted): static
    {
        $this->totalBudgeted = $totalBudgeted;

        return $this;
    }

    public function getTotalActual(): ?string
    {
        return $this->totalActual;
    }

    public function setTotalActual(?string $totalActual): static
    {
        $this->totalActual = $totalActual;

        return $this;
    }

    public function getLeftOver(): ?string
    {
        return $this->leftOver;
    }

    public function setLeftOver(?string $leftOver): static
    {
        $this->leftOver = $leftOver;

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

    public function getEditedByUser(): ?User
    {
        return $this->editedByUser;
    }

    public function setEditedByUser(?User $editedByUser): static
    {
        $this->editedByUser = $editedByUser;

        return $this;
    }

    /**
     * @return Collection<int, BudgetItem>
     */
    public function getBudgetItems(): Collection
    {
        return $this->budgetItems;
    }

    public function addBudgetItem(BudgetItem $budgetItem): static
    {
        if (!$this->budgetItems->contains($budgetItem)) {
            $this->budgetItems->add($budgetItem);
            $budgetItem->setBudget($this);
        }

        return $this;
    }

    public function removeBudgetItem(BudgetItem $budgetItem): static
    {
        if ($this->budgetItems->removeElement($budgetItem)) {
            // set the owning side to null (unless already changed)
            if ($budgetItem->getBudget() === $this) {
                $budgetItem->setBudget(null);
            }
        }

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
     * @return Collection<int, Purchase>
     */
    public function getPurchases(): Collection
    {
        return $this->purchases;
    }

    public function addPurchase(Purchase $purchase): static
    {
        if (!$this->purchases->contains($purchase)) {
            $this->purchases->add($purchase);
            $purchase->setBudget($this);
        }

        return $this;
    }

    public function removePurchase(Purchase $purchase): static
    {
        if ($this->purchases->removeElement($purchase)) {
            // set the owning side to null (unless already changed)
            if ($purchase->getBudget() === $this) {
                $purchase->setBudget(null);
            }
        }

        return $this;
    }
}
