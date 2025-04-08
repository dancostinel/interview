<?php

declare(strict_types=1);

namespace App\Dto;

class CommissionInfoDto
{
    private string $bin;
    private string $amount;
    private string $currency;

    public function __construct(string $bin, string $amount, string $currency)
    {
        $this->bin = $bin;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getBin(): string
    {
        return $this->bin;  #fix $value[0] = trim($p2[1], '"')
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
