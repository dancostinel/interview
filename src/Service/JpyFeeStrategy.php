<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\FeeInterface;
use App\Dto\OperationInfoDto;

class JpyFeeStrategy implements FeeInterface
{
    private const JPY_CURRENCY = 'JPY';
    private const XRS_JPY_EUR = 129.53;

    public function __construct(private CurrencyFeeBase $feeBase)
    {
    }

    public function supports(string $currency): bool
    {
        return self::JPY_CURRENCY === strtoupper($currency);
    }

    public function getFee(OperationInfoDto $dto): string
    {
        $jpyToEur = $dto->getAmount() / self::XRS_JPY_EUR;

        return $this->feeBase->getFeeForExchangeRateValue($dto, $jpyToEur);
    }
}
