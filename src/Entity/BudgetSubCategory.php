<?php

namespace App\Entity;

use App\Repository\BudgetSubCategoryRepository;
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
}
