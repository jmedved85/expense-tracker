<?php

namespace App\Entity;

enum AccountType: int
{
    case CASH = 1;
    case BANK = 2;
    case CREDIT_CARD = 3;
    case DEBIT_CARD = 4;
    case CRYPTO = 5;
    case INVESTMENT = 6;
    case LOAN = 7;
    case SAVINGS = 8;
    case WALLET = 9;

    public const CHOICES = [
        'Cash' => self::CASH,
        'Bank' => self::BANK,
        'Credit Card' => self::CREDIT_CARD,
        'Debit Card' => self::DEBIT_CARD,
        'Crypto' => self::CRYPTO,
        'Investment' => self::INVESTMENT,
        'Loan' => self::LOAN,
        'Savings' => self::SAVINGS,
        'Wallet' => self::WALLET,
    ];

    public static function getName(int $value): string
    {
        try {
            $case = self::from($value);

            foreach (self::CHOICES as $name => $type) {
                if ($type === $case) {
                    return $name;
                }
            }
        } catch (\UnexpectedValueException $e) {
            return 'Unknown';
        }
    }
}