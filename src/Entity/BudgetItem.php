<?php

namespace App\Entity;

use App\Repository\BudgetItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BudgetItemRepository::class)]
class BudgetItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $currency = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $budgeted = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $actual = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $leftOver = null;

    #[ORM\ManyToOne(inversedBy: 'budgetItems')]
    private ?Budget $budget = null;

    #[ORM\ManyToOne(inversedBy: 'budgetItems')]
    private ?BudgetSubCategory $budgetSubCategory = null;

    #[ORM\ManyToOne(inversedBy: 'budgetItems')]
    private ?GeneralCategory $generalCategory = null;

    #[ORM\ManyToOne(inversedBy: 'addedBudgetItems')]
    private ?User $addedByUser = null;

    #[ORM\ManyToOne(inversedBy: 'editedBudgetItems')]
    private ?User $editedByUser = null;

    #[ORM\ManyToOne(inversedBy: 'budgetItems')]
    private ?Unit $unit = null;

    public function __toString()
    {
        return (string) $this->getBudget()->getBudgetTypeName()
            . ' - '
            .  (string) ($this->getBudgetSubCategory() ? $this->getBudgetSubCategory()->getName()
                : $this->getGeneralCategory()->getName())
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBudgeted(): ?string
    {
        return $this->budgeted;
    }

    public function setBudgeted(?string $budgeted): static
    {
        $this->budgeted = $budgeted;

        return $this;
    }

    public function getActual(): ?string
    {
        return $this->actual;
    }

    public function setActual(?string $amount, bool $increase = null): static
    {
        $currentActual = floatval($this->actual);

        if ($increase) {
            $currentActual += floatval($amount);
        } else {
            $currentActual -= floatval($amount);
        }

        $this->actual = strval($currentActual);

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

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): static
    {
        $this->budget = $budget;

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
