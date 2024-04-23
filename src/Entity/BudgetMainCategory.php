<?php

namespace App\Entity;

use App\Repository\BudgetMainCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BudgetMainCategoryRepository::class)]
class BudgetMainCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: BudgetSubCategory::class, mappedBy: 'budgetMainCategory')]
    private Collection $budgetSubCategories;

    #[ORM\ManyToOne(inversedBy: 'budgetMainCategories')]
    private ?Unit $unit = null;

    public function __construct()
    {
        $this->budgetSubCategories = new ArrayCollection();
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

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, BudgetSubCategory>
     */
    public function getBudgetSubCategories(): Collection
    {
        return $this->budgetSubCategories;
    }

    public function addBudgetSubCategory(BudgetSubCategory $budgetSubCategory): static
    {
        if (!$this->budgetSubCategories->contains($budgetSubCategory)) {
            $this->budgetSubCategories->add($budgetSubCategory);
            $budgetSubCategory->setBudgetMainCategory($this);
        }

        return $this;
    }

    public function removeBudgetSubCategory(BudgetSubCategory $budgetSubCategory): static
    {
        if ($this->budgetSubCategories->removeElement($budgetSubCategory)) {
            // set the owning side to null (unless already changed)
            if ($budgetSubCategory->getBudgetMainCategory() === $this) {
                $budgetSubCategory->setBudgetMainCategory(null);
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
}
