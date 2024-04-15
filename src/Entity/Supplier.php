<?php

namespace App\Entity;

use App\Repository\SupplierRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SupplierRepository::class)]
class Supplier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $currency = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $mobileNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $contactName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $jobTitle = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $vatNumber = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2, nullable: true)]
    private ?string $vatRate = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $bankAccountName = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $bankAccountNumber = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $iban = null;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $sortCode = null;

    #[ORM\Column(length: 11, nullable: true)]
    private ?string $bicCode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $supplierTerms = null;

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

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getMobileNumber(): ?string
    {
        return $this->mobileNumber;
    }

    public function setMobileNumber(?string $mobileNumber): static
    {
        $this->mobileNumber = $mobileNumber;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    public function setContactName(?string $contactName): static
    {
        $this->contactName = $contactName;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): static
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    public function setVatNumber(?string $vatNumber): static
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    public function getVatRate(): ?string
    {
        return $this->vatRate;
    }

    public function setVatRate(?string $vatRate): static
    {
        $this->vatRate = $vatRate;

        return $this;
    }

    public function getBankAccountName(): ?string
    {
        return $this->bankAccountName;
    }

    public function setBankAccountName(?string $bankAccountName): static
    {
        $this->bankAccountName = $bankAccountName;

        return $this;
    }

    public function getBankAccountNumber(): ?string
    {
        return $this->bankAccountNumber;
    }

    public function setBankAccountNumber(?string $bankAccountNumber): static
    {
        $this->bankAccountNumber = $bankAccountNumber;

        return $this;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): static
    {
        $this->iban = $iban;

        return $this;
    }

    public function getSortCode(): ?string
    {
        return $this->sortCode;
    }

    public function setSortCode(?string $sortCode): static
    {
        $this->sortCode = $sortCode;

        return $this;
    }

    public function getBicCode(): ?string
    {
        return $this->bicCode;
    }

    public function setBicCode(?string $bicCode): static
    {
        $this->bicCode = $bicCode;

        return $this;
    }

    public function getSupplierTerms(): ?string
    {
        return $this->supplierTerms;
    }

    public function setSupplierTerms(?string $supplierTerms): static
    {
        $this->supplierTerms = $supplierTerms;

        return $this;
    }
}
