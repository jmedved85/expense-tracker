<?php

namespace App\Entity;

enum TransactionType: int
{
    case BANK_PAYMENT = 1;
    case BANK_TRANSFER = 2;
    case CASH_PAYMENT = 3;
    case CASH_WITHDRAWAL = 4;
    case CASH_TRANSFER = 7;
    case CREDIT_CARD_PAYMENT = 5;
    case DEBIT_CARD_PAYMENT = 6;
    case CURRENCY_EXCHANGE = 8;
    case ACCOUNT_CHARGE = 9;
    case FUNDS_TRANSFER = 10;
    case MONEY_RETURN = 11;

    public const NAMES = [
        'Bank Payment' => self::BANK_PAYMENT,
        'Bank Transfer' => self::BANK_TRANSFER,
        'Cash Payment' => self::CASH_PAYMENT,
        'Cash Withdrawal' => self::CASH_WITHDRAWAL,
        'Cash Transfer' => self::CASH_TRANSFER,
        'Credit Card Payment' => self::CREDIT_CARD_PAYMENT,
        'Debit Card Payment' => self::DEBIT_CARD_PAYMENT,
        'Currency Exchange' => self::CURRENCY_EXCHANGE,
        'Account Charge' => self::ACCOUNT_CHARGE,
        'Funds Transfer' => self::FUNDS_TRANSFER,
        'Money Return' => self::MONEY_RETURN,
    ];

    public static function getName(int $value): string
    {
        return match ($value) {
            self::BANK_PAYMENT->value => 'Bank Payment',
            self::BANK_TRANSFER->value => 'Bank Transfer',
            self::CASH_PAYMENT->value => 'Cash Payment',
            self::CASH_WITHDRAWAL->value => 'Cash Withdrawal',
            self::CASH_TRANSFER->value => 'Cash Transfer',
            self::CREDIT_CARD_PAYMENT->value => 'Credit Card Payment',
            self::DEBIT_CARD_PAYMENT->value => 'Debit Card Payment',
            self::CURRENCY_EXCHANGE->value => 'Currency Exchange',
            self::ACCOUNT_CHARGE->value => 'Account Charge',
            self::FUNDS_TRANSFER->value => 'Funds Transfer',
            self::MONEY_RETURN->value => 'Money Return',

            default => 'Unknown',
        };
    }
}
