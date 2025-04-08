<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ApiException;

class ApiCallService
{
    /**
     * @throws ApiException
     */
    public function getResponse(string $url): array
    {
        try {
            $apiResponse = file_get_contents($url);
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
