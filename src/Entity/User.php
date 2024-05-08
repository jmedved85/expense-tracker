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

    #[ORM\OneToMany(targetEntity: Purchase::class, mappedBy: 'addedByUser')]
    private Collection $addedPurchases;

    #[ORM\OneToMany(targetEntity: Purchase::class, mappedBy: 'editedByUser')]
    private Collection $editedPurchases;

    #[ORM\OneToMany(targetEntity: Invoice::class, mappedBy: 'addedByUser')]
    private Collection $addedInvoices;

    #[ORM\OneToMany(targetEntity: Invoice::class, mappedBy: 'editedByUser')]
    private Collection $editedInvoices;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'addedByUser')]
    private Collection $addedTransactions;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'editedByUser')]
    private Collection $editedTransactions;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'addedByUser')]
    private Collection $comments;

    public function __construct()
    {
        $this->addedBudgets = new ArrayCollection();
        $this->editedBudgets = new ArrayCollection();
        $this->addedBudgetItems = new ArrayCollection();
        $this->editedBudgetItems = new ArrayCollection();
        $this->userUnits = new ArrayCollection();
        $this->addedPurchases = new ArrayCollection();
        $this->editedPurchases = new ArrayCollection();
        $this->addedInvoices = new ArrayCollection();
        $this->editedInvoices = new ArrayCollection();
        $this->addedTransactions = new ArrayCollection();
        $this->editedTransactions = new ArrayCollection();
        $this->comments = new ArrayCollection();
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

    /**
     * @return Collection<int, Purchase>
     */
    public function getAddedPurchases(): Collection
    {
        return $this->addedPurchases;
    }

    public function addAddedPurchase(Purchase $addedPurchase): static
    {
        if (!$this->addedPurchases->contains($addedPurchase)) {
            $this->addedPurchases->add($addedPurchase);
            $addedPurchase->setAddedByUser($this);
        }

        return $this;
    }

    public function removeAddedPurchase(Purchase $addedPurchase): static
    {
        if ($this->addedPurchases->removeElement($addedPurchase)) {
            // set the owning side to null (unless already changed)
            if ($addedPurchase->getAddedByUser() === $this) {
                $addedPurchase->setAddedByUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Purchase>
     */
    public function getEditedPurchases(): Collection
    {
        return $this->editedPurchases;
    }

    public function addEditedPurchase(Purchase $editedPurchase): static
    {
        if (!$this->editedPurchases->contains($editedPurchase)) {
            $this->editedPurchases->add($editedPurchase);
            $editedPurchase->setEditedByUser($this);
        }

        return $this;
    }

    public function removeEditedPurchase(Purchase $editedPurchase): static
    {
        if ($this->editedPurchases->removeElement($editedPurchase)) {
            // set the owning side to null (unless already changed)
            if ($editedPurchase->getEditedByUser() === $this) {
                $editedPurchase->setEditedByUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getAddedInvoices(): Collection
    {
        return $this->addedInvoices;
    }

    public function addAddedInvoice(Invoice $addedInvoice): static
    {
        if (!$this->addedInvoices->contains($addedInvoice)) {
            $this->addedInvoices->add($addedInvoice);
            $addedInvoice->setAddedByUser($this);
        }

        return $this;
    }

    public function removeAddedInvoice(Invoice $addedInvoice): static
    {
        if ($this->addedInvoices->removeElement($addedInvoice)) {
            // set the owning side to null (unless already changed)
            if ($addedInvoice->getAddedByUser() === $this) {
                $addedInvoice->setAddedByUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getEditedInvoices(): Collection
    {
        return $this->editedInvoices;
    }

    public function addEditedInvoice(Invoice $editedInvoice): static
    {
        if (!$this->editedInvoices->contains($editedInvoice)) {
            $this->editedInvoices->add($editedInvoice);
            $editedInvoice->setEditedByUser($this);
        }

        return $this;
    }

    public function removeEditedInvoice(Invoice $editedInvoice): static
    {
        if ($this->editedInvoices->removeElement($editedInvoice)) {
            // set the owning side to null (unless already changed)
            if ($editedInvoice->getEditedByUser() === $this) {
                $editedInvoice->setEditedByUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getAddedTransactions(): Collection
    {
        return $this->addedTransactions;
    }

    public function addAddedTransaction(Transaction $addedTransaction): static
    {
        if (!$this->addedTransactions->contains($addedTransaction)) {
            $this->addedTransactions->add($addedTransaction);
            $addedTransaction->setAddedByUser($this);
        }

        return $this;
    }

    public function removeAddedTransaction(Transaction $addedTransaction): static
    {
        if ($this->addedTransactions->removeElement($addedTransaction)) {
            // set the owning side to null (unless already changed)
            if ($addedTransaction->getAddedByUser() === $this) {
                $addedTransaction->setAddedByUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getEditedTransactions(): Collection
    {
        return $this->editedTransactions;
    }

    public function addEditedTransaction(Transaction $editedTransaction): static
    {
        if (!$this->editedTransactions->contains($editedTransaction)) {
            $this->editedTransactions->add($editedTransaction);
            $editedTransaction->setEditedByUser($this);
        }

        return $this;
    }

    public function removeEditedTransaction(Transaction $editedTransaction): static
    {
        if ($this->editedTransactions->removeElement($editedTransaction)) {
            // set the owning side to null (unless already changed)
            if ($editedTransaction->getEditedByUser() === $this) {
                $editedTransaction->setEditedByUser(null);
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
            $comment->setAddedByUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAddedByUser() === $this) {
                $comment->setAddedByUser(null);
            }
        }

        return $this;
    }
}
