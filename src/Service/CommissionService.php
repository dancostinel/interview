<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\ApiResponseInterface;
use App\Dto\CommissionInfoDto;
use App\Exception\ApiException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CommissionService
{
    private const EUR_CURRENCY = 'EUR';

    public function __construct(
        private ApiResponseInterface $apiResponse,
        private MiscService $miscService,
        #[Autowire(env: 'json:EU_COUNTRY_CODES')] private array $euCountryCodes,
    ) {
    }

    /**
     * @throws ApiException
     */
    public function getCommissionValue(CommissionInfoDto $dto): float
    {
        $apiResponse = $this->apiResponse->getResponse($dto);
        if (self::EUR_CURRENCY === $dto->getCurrency() || $apiResponse->getRateValue() === 0.0) {
            $baseCommissionValue = (float) $dto->getAmount();
            $baseCommissionValue = (float) $this->miscService->getRoundedNumberUp($baseCommissionValue);
        }

        if (self::EUR_CURRENCY !== $dto->getCurrency() || $apiResponse->getRateValue() > 0.0) {
            $baseCommissionValue = ((float) $dto->getAmount()) / $apiResponse->getRateValue();
            $baseCommissionValue = (float) $this->miscService->getRoundedNumberUp($baseCommissionValue);
        }

        return $this->getBaseCommissionValue($apiResponse->getCountryCode(), $baseCommissionValue);
    }

    private function countryIsInEu(string $countryCode): bool
    {
        return in_array($countryCode, $this->euCountryCodes, true);
    }

    public function getBaseCommissionValue(string $countryCode, float $baseCommissionValue): float
    {
        $amountBasedOnCountry = $this->countryIsInEu($countryCode) ? 0.01 : 0.02;

        return $baseCommissionValue * $amountBasedOnCountry;
    }
}
