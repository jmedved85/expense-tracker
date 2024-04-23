<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sonata\UserBundle\Entity\BaseUser;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
class User extends BaseUser
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    protected $id;

    #[ORM\OneToMany(targetEntity: Budget::class, mappedBy: 'addedByUser')]
    private Collection $addedBudgets;

    #[ORM\OneToMany(targetEntity: Budget::class, mappedBy: 'editedByUser')]
    private Collection $editedBudgets;

    #[ORM\OneToMany(targetEntity: BudgetItem::class, mappedBy: 'addedByUser')]
    private Collection $addedBudgetItems;

    #[ORM\OneToMany(targetEntity: BudgetItem::class, mappedBy: 'editedByUser')]
    private Collection $editedBudgetItems;

    #[ORM\OneToMany(targetEntity: UserUnit::class, mappedBy: 'user')]
    private Collection $userUnits;

    public function __construct()
    {
        $this->addedBudgets = new ArrayCollection();
        $this->editedBudgets = new ArrayCollection();
        $this->addedBudgetItems = new ArrayCollection();
        $this->editedBudgetItems = new ArrayCollection();
        $this->userUnits = new ArrayCollection();
    }

    /**
     * @return Collection<int, Budget>
     */
    public function getAddedBudgets(): Collection
    {
        return $this->addedBudgets;
    }

    public function addAddedBudget(Budget $addedBudget): static
    {
        if (!$this->addedBudgets->contains($addedBudget)) {
            $this->addedBudgets->add($addedBudget);
            $addedBudget->setAddedByUser($this);
        }

        return $this;
    }

    public function removeAddedBudget(Budget $addedBudget): static
    {
        if ($this->addedBudgets->removeElement($addedBudget)) {
            // set the owning side to null (unless already changed)
            if ($addedBudget->getAddedByUser() === $this) {
                $addedBudget->setAddedByUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Budget>
     */
    public function getEditedBudgets(): Collection
    {
        return $this->editedBudgets;
    }

    public function addEditedBudget(Budget $editedBudget): static
    {
        if (!$this->editedBudgets->contains($editedBudget)) {
            $this->editedBudgets->add($editedBudget);
            $editedBudget->setEditedByUser($this);
        }

        return $this;
    }

    public function removeEditedBudget(Budget $editedBudget): static
    {
        if ($this->editedBudgets->removeElement($editedBudget)) {
            // set the owning side to null (unless already changed)
            if ($editedBudget->getEditedByUser() === $this) {
                $editedBudget->setEditedByUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BudgetItem>
     */
    public function getAddedBudgetItems(): Collection
    {
        return $this->addedBudgetItems;
    }

    public function addAddedBudgetItem(BudgetItem $addedBudgetItem): static
    {
        if (!$this->addedBudgetItems->contains($addedBudgetItem)) {
            $this->addedBudgetItems->add($addedBudgetItem);
            $addedBudgetItem->setAddedByUser($this);
        }

        return $this;
    }

    public function removeAddedBudgetItem(BudgetItem $addedBudgetItem): static
    {
        if ($this->addedBudgetItems->removeElement($addedBudgetItem)) {
            // set the owning side to null (unless already changed)
            if ($addedBudgetItem->getAddedByUser() === $this) {
                $addedBudgetItem->setAddedByUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BudgetItem>
     */
    public function getEditedBudgetItems(): Collection
    {
        return $this->editedBudgetItems;
    }

    public function addEditedBudgetItem(BudgetItem $editedBudgetItem): static
    {
        if (!$this->editedBudgetItems->contains($editedBudgetItem)) {
            $this->editedBudgetItems->add($editedBudgetItem);
            $editedBudgetItem->setEditedByUser($this);
        }

        return $this;
    }

    public function removeEditedBudgetItem(BudgetItem $editedBudgetItem): static
    {
        if ($this->editedBudgetItems->removeElement($editedBudgetItem)) {
            // set the owning side to null (unless already changed)
            if ($editedBudgetItem->getEditedByUser() === $this) {
                $editedBudgetItem->setEditedByUser(null);
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
            $userUnit->setUser($this);
        }

        return $this;
    }

    public function removeUserUnit(UserUnit $userUnit): static
    {
        if ($this->userUnits->removeElement($userUnit)) {
            // set the owning side to null (unless already changed)
            if ($userUnit->getUser() === $this) {
                $userUnit->setUser(null);
            }
        }

        return $this;
    }
}
