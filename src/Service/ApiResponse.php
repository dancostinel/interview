<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\ApiResponseInterface;
use App\Dto\CommissionInfoDto;
use App\Exception\ApiException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ApiResponse implements ApiResponseInterface
{
    private string $countryCode;
    private float $rateValue;

    public function __construct(
        private ApiCallService $apiCallService,
        #[Autowire(env: 'string:CREDIT_CARD_API_URL')] private string $creditCardApiUrl,
        #[Autowire(env: 'string:XRS_API_URL')] private string $xrsApiUrl,
    ) {
    }

    /**
     * @throws ApiException
     */
    public function getResponse(CommissionInfoDto $dto): self
    {
        $creditCardApiResponse = $this->makeApiCall($this->creditCardApiUrl . $dto->getBin());
        $this->countryCode = $creditCardApiResponse['country']['alpha2'] ?? '';

        $xrsApiResponse = $this->makeApiCall($this->xrsApiUrl);
        $this->rateValue = (float) ($xrsApiResponse['rates'][$dto->getCurrency()] ?? 0.0);

        return $this;
    }

    /**
     * @throws ApiException
     */
    public function makeApiCall(string $url): array
    {
        return $this->apiCallService->getResponse($url);
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getRateValue(): float
    {
        return $this->rateValue;
    }
}
