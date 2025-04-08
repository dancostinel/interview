<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\MoneyOperationInterface;
use App\Dto\OperationInfoDto;
use App\Exception\ReadCsvException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class FeeService
{
    public function __construct(
        #[AutowireIterator('money_operation_strategy')] private iterable $strategies
    ) {
    }

    /**
     * @throws ReadCsvException
     */
    public function getFeeValue(OperationInfoDto $dto): array
    {
        /** @var MoneyOperationInterface $strategy */
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($dto->getOperationType())) {
                $fees[] = $strategy->handle($dto);
            }
        }

        return $fees ?? [];
    }
}
