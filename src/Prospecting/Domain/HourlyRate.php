<?php

namespace Mannion007\BestInvestments\Prospecting\Domain;

use Mannion007\ValueObjects\Money;
use Mannion007\ValueObjects\Currency;

class HourlyRate extends Money
{
    public function __construct(int $amount, Currency $currency)
    {
        if ($currency->isNot(Currency::gbp())) {
            throw new \InvalidArgumentException('Only GBP currency is allowed');
        }
        parent::__construct($amount, $currency);
    }
}
