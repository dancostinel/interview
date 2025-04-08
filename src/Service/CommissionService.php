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
        #[Autowire('%kernel.project_dir%/public/input.txt')] private string $inputJson,
        #[Autowire(env: 'json:EU_COUNTRY_CODES')] private array $euCountryCodes,
    ) {
    }

    /**
     * @throws ApiException
     */
    public function getCommissionValue(CommissionInfoDto $dto): float
    {
        $apiResponse = $this->apiResponse->getResponse($dto);
        if (!$this->countryIsInEu($apiResponse->getCountryCode())) {
            $baseCommissionValue = $this->getBaseCommissionValue($dto, $this->apiResponse->getRateValue());
        }

        if (!isset($baseCommissionValue)) {
            return $this->countryIsInEu($dto->getCurrency()) ? 0.01 : 0.02;
        }

        return $this->countryIsInEu($dto->getCurrency()) ? (float) $baseCommissionValue * 0.01 : $baseCommissionValue * 0.02;
    }

    private function countryIsInEu(string $countryCode): bool
    {
        return in_array($countryCode, $this->euCountryCodes, true);
    }

    private function getBaseCommissionValue(CommissionInfoDto $dto, float $rate): float
    {
        if (self::EUR_CURRENCY === $dto->getCurrency() || $rate === 0.0) {
            return (float) $this->miscService->getRoundedNumberUp((float) $dto->getAmount());
        }

        return (float) $this->miscService->getRoundedNumberUp(((float) $dto->getAmount() / $rate));
    }
}
