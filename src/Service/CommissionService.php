<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CommissionInfoDto;
use App\Exception\ApiException;
use App\Exception\ReadTxtException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CommissionService
{
    private const EUR_CURRENCY = 'EUR';

    public function __construct(
        private ApiService $apiService,
        #[Autowire('%kernel.project_dir%/public/input.txt')] private string $inputJson,
        #[Autowire(env: 'string:CREDIT_CARD_API_URL')] private string $creditCardApiUrl,
        #[Autowire(env: 'string:XRS_API_URL')] private string $xrsApiUrl,
        #[Autowire(env: 'json:EU_COUNTRY_CODES')] private array $euCountryCodes,
    ) {
    }

    /**
     * @throws ReadTxtException
     * @throws ApiException
     */
    public function getCommissionValue(CommissionInfoDto $dto): string
    {
        $creditCardApiResponse = $this->apiService->getApiResponse($this->creditCardApiUrl . $dto->getBin());
        $xrsApiResponse = $this->apiService->getApiResponse($this->xrsApiUrl);

        if (!$this->countryIsInEu($creditCardApiResponse['country']['alpha2'] ?? '')) {
            return $this->getCommissionForNonEuCountry($dto, $xrsApiResponse);
        }

        return $this->countryIsInEu($dto->getCurrency()) ? "0.01\n" : "0.02\n";
    }

    private function countryIsInEu(string $countryCode): bool
    {
        return in_array($countryCode, $this->euCountryCodes, true);
    }

    private function getCommissionForNonEuCountry(CommissionInfoDto $dto, array $xrsApiResponse): string
    {
        $rate = (float) ($xrsApiResponse['rates'][$dto->getCurrency()] ?? 0.0);
        if (self::EUR_CURRENCY === $dto->getCurrency() || $rate === 0.0) {
            return $dto->getAmount() . "\n";
        }

        return (string) ((float) $dto->getAmount() / $rate) . "\n";
    }
}
