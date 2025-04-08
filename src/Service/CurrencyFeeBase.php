<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\OperationInfoDto;
use App\Exception\ReadCsvException;
use App\Traits\PrivateFeeTrait;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\CacheItem;

class CurrencyFeeBase
{
    use PrivateFeeTrait;

    private const USD_CURRENCY = 'USD';
    private const FREE_OF_CHARGE_COUNT = 3;
    private const FREE_OF_CHARGE_LIMIT = 1000;
    private const XRS_USD_EUR = 1.1497;

    public function __construct(
        private RedisService $redisService,
        private DateIntervalService $dateIntervalService,
        private MiscService $miscService,
    ) {
    }

    public function getFeeForExchangeRateValue(OperationInfoDto $dto, float $value): string
    {
        $redisKey = $this->dateIntervalService->compute($dto);
        $cacheItem = $this->redisService->read($redisKey);
        if (null === $cacheItem && $value <= self::FREE_OF_CHARGE_LIMIT) {
            $this->saveRedisData($value, $value, 1, false, $redisKey);

            return '0.00';
        }

        if (null === $cacheItem && $value > self::FREE_OF_CHARGE_LIMIT) {
            $this->saveRedisData($value, $value, 1, true, $redisKey);
            $fee = ($value - self::FREE_OF_CHARGE_LIMIT) / 100 * self::WITHDRAW_PRIVATE_FEE;

            return $this->miscService->getRoundedNumberUp($fee);
        }

        return $this->getFeeForMultipleUserEntries($dto, $cacheItem, $redisKey);
    }

    private function saveRedisData(
        float|string $totalAmount,
        float|string $currentValue,
        int $count,
        bool $hasReachedFreeLimit,
        string $redisKey,
    ): void {
        $data = [
            'total_amount' => $totalAmount,
            'amount' => $currentValue,
            'count' => $count,
            'has_reached_free_limit' => $hasReachedFreeLimit,
        ];
        $this->redisService->save($redisKey, $data);
    }

    private function saveRedisDataForExceedingLimit(
        OperationInfoDto $dto,
        float $total,
        int $count,
        string $redisKey
    ): void {
        $this->saveRedisData(
            $this->miscService->getRoundedNumberUp($total),
            $this->miscService->getRoundedNumberUp($dto->getAmount()),
            $count,
            true,
            $redisKey
        );
    }

    private function getFeeForMultipleUserEntries(
        OperationInfoDto $dto,
        CacheItemInterface $cacheItem,
        string $redisKey,
    ): string {
        $cachedData = $cacheItem->get();
        $total = (float) $cachedData['total_amount'] + $dto->getAmount();
        if ($cachedData['has_reached_free_limit'] || $cachedData['count'] >= self::FREE_OF_CHARGE_COUNT) {
            $this->saveRedisDataForExceedingLimit($dto, $total, $cachedData['count'] + 1, $redisKey);
            $fee = $dto->getAmount() / 100 * self::WITHDRAW_PRIVATE_FEE;

            return $this->miscService->getRoundedNumberUp($fee);
        }

        if ($total > self::FREE_OF_CHARGE_LIMIT) {
            $this->saveRedisDataForExceedingLimit($dto, $total, $cachedData['count'] + 1, $redisKey);
            $commissionAmount = $total - self::FREE_OF_CHARGE_LIMIT;
            $fee = $commissionAmount / 100 * self::WITHDRAW_PRIVATE_FEE;

            return $this->miscService->getRoundedNumberUp($fee);
        }

        return '0.00';
    }
}
