<?php

namespace App\Traits;

use App\Exception\ReadCsvException;

trait PrivateFeeTrait
{
    private const WITHDRAW_PRIVATE_FEE = 0.3;
    private const FREE_OF_CHARGE_LIMIT = 1000;

    /**
     * @throws ReadCsvException
     */
    public function calculate(float $amount): string
    {
        if (!isset($this->miscService)){
            throw new ReadCsvException('Unable to perform calculation of fee');
        }

        if ($amount <= 1000) {
            return '0.00';
        }

        $commissionedAmount = $amount - self::FREE_OF_CHARGE_LIMIT;
        $fee = ($commissionedAmount / 100) * self::WITHDRAW_PRIVATE_FEE;

        return $this->miscService->getRoundedNumberUp($fee);
    }
}
