<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class MiscService
{
    private const BASE_EXPONENT_VALUE = 10;

    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function getRoundedNumberUp(float $number, int $precision = 2): string
    {
        $exponent = pow(self::BASE_EXPONENT_VALUE, $precision);
        $celled = ceil($number * $exponent) / $exponent;

        return number_format($celled, 2);
    }
}
