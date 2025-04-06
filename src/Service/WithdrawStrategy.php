<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\FeeInterface;
use App\Contract\MoneyOperationInterface;
use App\Dto\OperationInfoDto;
use App\Exception\ReadCsvException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class WithdrawStrategy implements MoneyOperationInterface
{
    private const WITHDRAW_OPERATION = 'withdraw';
    private const WITHDRAW_BUSINESS = 'business';
    private const WITHDRAW_BUSINESS_FEE = 0.5;

    public function __construct(
        private MiscService $miscService,
        #[AutowireIterator('fee_strategy')] private iterable $strategies,
    ) {
    }

    public function supports(string $operation): bool
    {
        return self::WITHDRAW_OPERATION === $operation;
    }

    /**
     * @throws ReadCsvException
     */
    public function handle(OperationInfoDto $dto): string
    {
        if (self::WITHDRAW_BUSINESS === $dto->getUserType()) {
            return $this->getBusinessFee($dto);
        }

        /** @var FeeInterface $feeStrategy */
        foreach ($this->strategies as $feeStrategy) {
            if ($feeStrategy->supports($dto->getCurrency())) {
                return $feeStrategy->getFee($dto);
            }
        }

        throw new ReadCsvException('Unsupported currency ' . $dto->getCurrency());
    }

    private function getBusinessFee(OperationInfoDto $dto): string
    {
        $businessCommissionFee = ($dto->getAmount() / 100) * self::WITHDRAW_BUSINESS_FEE;

        return $this->miscService->getRoundedNumberUp($businessCommissionFee);
    }
}
