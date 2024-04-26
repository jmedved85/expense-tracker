<?php

namespace App\Entity;

use App\Repository\BudgetSubCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BudgetSubCategoryRepository::class)]
class BudgetSubCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'budgetSubCategories')]
    private ?BudgetMainCategory $budgetMainCategory = null;

    #[ORM\OneToMany(targetEntity: BudgetItem::class, mappedBy: 'budgetSubCategory')]
    private Collection $budgetItems;

    #[ORM\ManyToOne(inversedBy: 'budgetSubCategories')]
    private ?Unit $unit = null;

    #[ORM\OneToMany(targetEntity: PurchaseLine::class, mappedBy: 'budgetSubCategory')]
    private Collection $purchaseLines;

    public function __construct()
    {
        $this->budgetItems = new ArrayCollection();
        $this->purchaseLines = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBudgetMainCategory(): ?BudgetMainCategory
    {
        return $this->budgetMainCategory;
    }

    public function setBudgetMainCategory(?BudgetMainCategory $budgetMainCategory): static
    {
        $this->budgetMainCategory = $budgetMainCategory;

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
            $budgetItem->setBudgetSubCategory($this);
        }

        return $this;
    }

    public function removeBudgetItem(BudgetItem $budgetItem): static
    {
        if ($this->budgetItems->removeElement($budgetItem)) {
            // set the owning side to null (unless already changed)
            if ($budgetItem->getBudgetSubCategory() === $this) {
                $budgetItem->setBudgetSubCategory(null);
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
            $purchaseLine->setBudgetSubCategory($this);
        }

        return $this;
    }

    public function removePurchaseLine(PurchaseLine $purchaseLine): static
    {
        if ($this->purchaseLines->removeElement($purchaseLine)) {
            // set the owning side to null (unless already changed)
            if ($purchaseLine->getBudgetSubCategory() === $this) {
                $purchaseLine->setBudgetSubCategory(null);
            }
        }

        return $this;
    }
}
