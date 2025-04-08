<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\FeeInterface;
use App\Dto\OperationInfoDto;

class EurFeeStrategy implements FeeInterface
{
    private const EUR_CURRENCY = 'EUR';

    public function __construct(private CurrencyFeeBase $feeBase)
    {
    }

    public function supports(string $currency): bool
    {
        return self::EUR_CURRENCY === strtoupper($currency);
    }

    public function getFee(OperationInfoDto $dto): string
    {
        return $this->feeBase->getFeeForExchangeRateValue($dto, $dto->getAmount());
    }
}
