<?php

declare(strict_types=1);

namespace App\Contract;

interface ApiResponseInterface
{
    public function makeApiCall(string $url): array;

    public function getCountryCode(): string;

    public function getRateValue(): float;
}
