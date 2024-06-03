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

    public const NAMES_STRING = [
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

    public const NAMES_VALUE = [
        'Cash' => self::CASH->value,
        'Bank' => self::BANK->value,
        'Credit Card' => self::CREDIT_CARD->value,
        'Debit Card' => self::DEBIT_CARD->value,
        'Crypto' => self::CRYPTO->value,
        'Investment' => self::INVESTMENT->value,
        'Loan' => self::LOAN->value,
        'Savings' => self::SAVINGS->value,
        'Wallet' => self::WALLET->value,
    ];

    public static function getName(int $value): string
    {
        return match ($value) {
            self::CASH->value => 'Cash',
            self::BANK->value => 'Bank',
            self::CREDIT_CARD->value => 'Credit Card',
            self::DEBIT_CARD->value => 'Debit Card',
            self::CRYPTO->value => 'Crypto',
            self::INVESTMENT->value => 'Investment',
            self::LOAN->value => 'Loan',
            self::SAVINGS->value => 'Savings',
            self::WALLET->value => 'Wallet',

            default => 'Unknown',
        };
    }
}
