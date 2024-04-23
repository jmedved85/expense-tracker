<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use NumberFormatter;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
class Account
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private $name;

    #[ORM\Column(type: Types::INTEGER)]
    private int $accountType;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private $balance;

    #[ORM\Column(type: Types::STRING, length: 3)]
    private $currency;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private $deactivated;

    #[ORM\ManyToOne(inversedBy: 'accounts')]
    private ?Unit $unit = null;

    public function __construct()
    {
        $this->balance = 0;
    }

    public function __toString()
    {
        return $this->name . ' ' . '(' . $this->getCurrency() . ')';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAccountType(): AccountType
    {
        return AccountType::from($this->accountType);
    }

    public function getAccountTypeName(): string
    {
        return AccountType::getName($this->accountType);
    }

    public function setAccountType(AccountType $accountType): self
    {
        $this->accountType = $accountType->value;

        return $this;
    }

    public function getNameWithCurrency(): ?string
    {
        return $this->name . ' ' . '(' . $this->getCurrency() . ')';
    }

    public function getNameWithCurrencyBalance(): ?string
    {
        $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
        $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $this->getCurrency());
        $accountCurrencySymbol = $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);

        if ($this->getCurrency() === 'EUR' || $this->getCurrency() === 'USD' || $this->getCurrency() === 'GBP') {
            $formattedBalanceValueDisplay =
                $accountCurrencySymbol . number_format(floatval($this->getBalance()), 2, '.', ',');
        } else {
            $formattedBalanceValueDisplay =
                $accountCurrencySymbol . ' ' . number_format(floatval($this->getBalance()), 2, '.', ',');
        }

        return $this->name . ' ' . '(' . $this->getCurrency() . ') (Balance: ' . $formattedBalanceValueDisplay . ')';
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(?string $amount, bool $increase = null): self
    {
        $currentBalance = floatval($this->getBalance());

        if ($increase) {
            $currentBalance += floatval($amount);
        } elseif ($increase === false) {
            $currentBalance -= floatval($amount);
        } else {
            $currentBalance = floatval($amount);
        }

        $this->balance = strval($currentBalance);

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function isDeactivated(): ?bool
    {
        return $this->deactivated;
    }

    public function setDeactivated(?bool $deactivated): self
    {
        $this->deactivated = $deactivated;

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
