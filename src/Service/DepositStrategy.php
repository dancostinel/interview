<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\MoneyOperationInterface;
use App\Dto\OperationInfoDto;

class DepositStrategy implements MoneyOperationInterface
{
    private const DEPOSIT_OPERATION = 'deposit';
    private const DEPOSIT_FEE = 0.03;

    public function __construct(private MiscService $miscService)
    {
    }

    public function supports(string $operation): bool
    {
        return self::DEPOSIT_OPERATION === $operation;
    }

    public function handle(OperationInfoDto $dto): string
    {
        $commissionFee = ($dto->getAmount() / 100) * self::DEPOSIT_FEE;

        return $this->miscService->getRoundedNumberUp($commissionFee);
    }
}
