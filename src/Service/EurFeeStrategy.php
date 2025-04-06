<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\FeeInterface;
use App\Dto\OperationInfoDto;
use App\Exception\ReadCsvException;
use App\Traits\PrivateFeeTrait;

class EurFeeStrategy implements FeeInterface
{
    use PrivateFeeTrait;

    private const EUR_CURRENCY = 'EUR';
    private const FREE_OF_CHARGE_LIMIT = 1000;
    private const FREE_OF_CHARGE_COUNT = 3;
    private const WITHDRAW_PRIVATE_FEE = 0.3;

    public function __construct(
        private RedisService $redisService,
        private DateIntervalService $dateIntervalService,
        private MiscService $miscService,
    ) {
    }

    public function supports(string $currency): bool
    {
        return self::EUR_CURRENCY === strtoupper($currency);
    }

    /**
     * @throws ReadCsvException
     */
    public function getFee(OperationInfoDto $dto): string
    {
        $redisKey = $this->dateIntervalService->compute($dto);
        $cacheItem = $this->redisService->read($redisKey);
        if (null === $cacheItem && $dto->getAmount() <= self::FREE_OF_CHARGE_LIMIT) {
            $data = [
                'total_amount' => $this->miscService->getRoundedNumberUp($dto->getAmount()),
                'amount' => $this->miscService->getRoundedNumberUp($dto->getAmount()),
                'count' => 1,
                'has_reached_free_limit' => false,
            ];
            $this->redisService->save($redisKey, $data);

            return '0.00';
        }

        if (null === $cacheItem && $dto->getAmount() > self::FREE_OF_CHARGE_LIMIT) {
            $data = [
                'total_amount' => $this->miscService->getRoundedNumberUp($dto->getAmount()),
                'amount' => $this->miscService->getRoundedNumberUp($dto->getAmount()),
                'count' => 1,
                'has_reached_free_limit' => true,
            ];
            $this->redisService->save($redisKey, $data);
            $commissionAmount = $dto->getAmount() - self::FREE_OF_CHARGE_LIMIT;
            $fee = $commissionAmount / 100 * self::WITHDRAW_PRIVATE_FEE;

            return $this->miscService->getRoundedNumberUp($fee);
        }

        $cachedData = $cacheItem->get();
        if ($cachedData['has_reached_free_limit']) {
            $data = [
                'total_amount' => $this->miscService->getRoundedNumberUp((float) $cachedData['total_amount'] + $dto->getAmount()),
                'amount' => $this->miscService->getRoundedNumberUp($dto->getAmount()),
                'count' => $cachedData['count'] + 1,
                'has_reached_free_limit' => true,
            ];
            $this->redisService->save($redisKey, $data);
            $fee = $dto->getAmount() / 100 * self::WITHDRAW_PRIVATE_FEE;

            return $this->miscService->getRoundedNumberUp($fee);
        }

        $total = (float) $cachedData['total_amount'] + $dto->getAmount();
        if ($total > 1000) {
            $data = [
                'total_amount' => $this->miscService->getRoundedNumberUp($total),
                'amount' => $this->miscService->getRoundedNumberUp($dto->getAmount()),
                'count' => $cachedData['count'] + 1,
                'has_reached_free_limit' => true,
            ];
            $this->redisService->save($redisKey, $data);
            $commissionAmount = $total - 1000;
            $fee = $commissionAmount / 100 * self::WITHDRAW_PRIVATE_FEE;

            return $this->miscService->getRoundedNumberUp($fee);
        }

        if ($cachedData['count'] >= 3) {
            $data = [
                'total_amount' => $this->miscService->getRoundedNumberUp((float) $cachedData['total_amount'] + $dto->getAmount()),
                'amount' => $this->miscService->getRoundedNumberUp($dto->getAmount()),
                'count' => $cachedData['count'] + 1,
                'has_reached_free_limit' => true,
            ];
            $this->redisService->save($redisKey, $data);
            $fee = $dto->getAmount() / 100 * self::WITHDRAW_PRIVATE_FEE;

            return $this->miscService->getRoundedNumberUp($fee);
        }

        throw new ReadCsvException('This should not be reached');
    }
}
