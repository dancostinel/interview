<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ApiException;

class ApiService
{
    /**
     * @throws ApiException
     */
    public function getApiResponse(string $apiUrl): array
    {
        try {
            $apiResponse = file_get_contents($apiUrl);
        } catch (\ErrorException $exception) {
            throw new ApiException('Too many requests');
        }

        if (empty($apiResponse)) {
            throw new ApiException('Empty response from API');
        }

        $apiToArray = json_decode($apiResponse, true);
        if (empty($apiToArray)) {
            throw new ApiException('Error response from card info API');
        }

        return $apiToArray;
    }
}