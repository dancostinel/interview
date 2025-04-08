<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\FeeInterface;
use App\Dto\OperationInfoDto;

class UsdFeeStrategy implements FeeInterface
{
    private const USD_CURRENCY = 'USD';
    private const XRS_USD_EUR = 1.1497;

    public function __construct(private CurrencyFeeBase $feeBase)
    {
    }

    public function supports(string $currency): bool
    {
        return self::USD_CURRENCY === strtoupper($currency);
    }

    public function getFee(OperationInfoDto $dto): string
    {
        $usdToEur = $dto->getAmount() / self::XRS_USD_EUR;

        return $this->feeBase->getFeeForExchangeRateValue($dto, $usdToEur);
    }
}
