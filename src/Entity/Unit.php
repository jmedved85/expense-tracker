<?php

namespace App\Entity;

use App\Repository\UnitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnitRepository::class)]
class Unit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: Account::class, mappedBy: 'unit')]
    private Collection $accounts;

    #[ORM\OneToMany(targetEntity: Budget::class, mappedBy: 'unit')]
    private Collection $budgets;

    #[ORM\OneToMany(targetEntity: BudgetItem::class, mappedBy: 'unit')]
    private Collection $budgetItems;

    #[ORM\OneToMany(targetEntity: BudgetMainCategory::class, mappedBy: 'unit')]
    private Collection $budgetMainCategories;

    #[ORM\OneToMany(targetEntity: BudgetSubCategory::class, mappedBy: 'unit')]
    private Collection $budgetSubCategories;

    #[ORM\OneToMany(targetEntity: Department::class, mappedBy: 'unit')]
    private Collection $departments;

    #[ORM\OneToMany(targetEntity: GeneralCategory::class, mappedBy: 'unit')]
    private Collection $generalCategories;

    #[ORM\OneToMany(targetEntity: Supplier::class, mappedBy: 'unit')]
    private Collection $suppliers;

    #[ORM\OneToMany(targetEntity: UserUnit::class, mappedBy: 'unit')]
    private Collection $userUnits;

    #[ORM\OneToMany(targetEntity: Purchase::class, mappedBy: 'unit')]
    private Collection $purchases;

    /**
     * @var Collection<int, Invoice>
     */
    #[ORM\OneToMany(targetEntity: Invoice::class, mappedBy: 'unit')]
    private Collection $invoices;

    public function __construct()
    {
        $this->accounts = new ArrayCollection();
        $this->budgets = new ArrayCollection();
        $this->budgetItems = new ArrayCollection();
        $this->budgetMainCategories = new ArrayCollection();
        $this->budgetSubCategories = new ArrayCollection();
        $this->departments = new ArrayCollection();
        $this->generalCategories = new ArrayCollection();
        $this->suppliers = new ArrayCollection();
        $this->userUnits = new ArrayCollection();
        $this->purchases = new ArrayCollection();
        $this->invoices = new ArrayCollection();
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

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

    /**
     * @return Collection<int, Account>
     */
    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    public function addAccount(Account $account): static
    {
        if (!$this->accounts->contains($account)) {
            $this->accounts->add($account);
            $account->setUnit($this);
        }

        return $this;
    }

    public function removeAccount(Account $account): static
    {
        if ($this->accounts->removeElement($account)) {
            // set the owning side to null (unless already changed)
            if ($account->getUnit() === $this) {
                $account->setUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Budget>
     */
    public function getBudgets(): Collection
    {
        return $this->budgets;
    }

    public function addBudget(Budget $budget): static
    {
        if (!$this->budgets->contains($budget)) {
            $this->budgets->add($budget);
            $budget->setUnit($this);
        }

        return $this;
    }

    public function removeBudget(Budget $budget): static
    {
        if ($this->budgets->removeElement($budget)) {
            // set the owning side to null (unless already changed)
            if ($budget->getUnit() === $this) {
                $budget->setUnit(null);
            }
        }

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
            $budgetItem->setUnit($this);
        }

        return $this;
    }

    public function removeBudgetItem(BudgetItem $budgetItem): static
    {
        if ($this->budgetItems->removeElement($budgetItem)) {
            // set the owning side to null (unless already changed)
            if ($budgetItem->getUnit() === $this) {
                $budgetItem->setUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BudgetMainCategory>
     */
    public function getBudgetMainCategories(): Collection
    {
        return $this->budgetMainCategories;
    }

    public function addBudgetMainCategory(BudgetMainCategory $budgetMainCategory): static
    {
        if (!$this->budgetMainCategories->contains($budgetMainCategory)) {
            $this->budgetMainCategories->add($budgetMainCategory);
            $budgetMainCategory->setUnit($this);
        }

        return $this;
    }

    public function removeBudgetMainCategory(BudgetMainCategory $budgetMainCategory): static
    {
        if ($this->budgetMainCategories->removeElement($budgetMainCategory)) {
            // set the owning side to null (unless already changed)
            if ($budgetMainCategory->getUnit() === $this) {
                $budgetMainCategory->setUnit(null);
            }
        }

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
            $budgetSubCategory->setUnit($this);
        }

        return $this;
    }

    public function removeBudgetSubCategory(BudgetSubCategory $budgetSubCategory): static
    {
        if ($this->budgetSubCategories->removeElement($budgetSubCategory)) {
            // set the owning side to null (unless already changed)
            if ($budgetSubCategory->getUnit() === $this) {
                $budgetSubCategory->setUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Department>
     */
    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    public function addDepartment(Department $department): static
    {
        if (!$this->departments->contains($department)) {
            $this->departments->add($department);
            $department->setUnit($this);
        }

        return $this;
    }

    public function removeDepartment(Department $department): static
    {
        if ($this->departments->removeElement($department)) {
            // set the owning side to null (unless already changed)
            if ($department->getUnit() === $this) {
                $department->setUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GeneralCategory>
     */
    public function getGeneralCategories(): Collection
    {
        return $this->generalCategories;
    }

    public function addGeneralCategory(GeneralCategory $generalCategory): static
    {
        if (!$this->generalCategories->contains($generalCategory)) {
            $this->generalCategories->add($generalCategory);
            $generalCategory->setUnit($this);
        }

        return $this;
    }

    public function removeGeneralCategory(GeneralCategory $generalCategory): static
    {
        if ($this->generalCategories->removeElement($generalCategory)) {
            // set the owning side to null (unless already changed)
            if ($generalCategory->getUnit() === $this) {
                $generalCategory->setUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Supplier>
     */
    public function getSuppliers(): Collection
    {
        return $this->suppliers;
    }

    public function addSupplier(Supplier $supplier): static
    {
        if (!$this->suppliers->contains($supplier)) {
            $this->suppliers->add($supplier);
            $supplier->setUnit($this);
        }

        return $this;
    }

    public function removeSupplier(Supplier $supplier): static
    {
        if ($this->suppliers->removeElement($supplier)) {
            // set the owning side to null (unless already changed)
            if ($supplier->getUnit() === $this) {
                $supplier->setUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserUnit>
     */
    public function getUserUnits(): Collection
    {
        return $this->userUnits;
    }

    public function addUserUnit(UserUnit $userUnit): static
    {
        if (!$this->userUnits->contains($userUnit)) {
            $this->userUnits->add($userUnit);
            $userUnit->setUnit($this);
        }

        return $this;
    }

    public function removeUserUnit(UserUnit $userUnit): static
    {
        if ($this->userUnits->removeElement($userUnit)) {
            // set the owning side to null (unless already changed)
            if ($userUnit->getUnit() === $this) {
                $userUnit->setUnit(null);
            }
        }

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
            $purchase->setUnit($this);
        }

        return $this;
    }

    public function removePurchase(Purchase $purchase): static
    {
        if ($this->purchases->removeElement($purchase)) {
            // set the owning side to null (unless already changed)
            if ($purchase->getUnit() === $this) {
                $purchase->setUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): static
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setUnit($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getUnit() === $this) {
                $invoice->setUnit(null);
            }
        }

        return $this;
    }
}
