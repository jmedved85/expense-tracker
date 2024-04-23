<?php

namespace App\Entity;

use App\Repository\UserUnitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserUnitRepository::class)]
class UserUnit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userUnits')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userUnits')]
    private ?Unit $unit = null;

    #[ORM\Column(nullable: true)]
    private ?int $memberType = null;

    #[ORM\Column(nullable: true)]
    private ?int $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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

    public function getMemberType(): ?int
    {
        return $this->memberType;
    }

    public function setMemberType(?int $memberType): static
    {
        $this->memberType = $memberType;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): static
    {
        $this->status = $status;

        return $this;
    }
}
