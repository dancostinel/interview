<?php

declare(strict_types=1);

namespace App\Contract;

use App\Dto\OperationInfoDto;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'money_operation_strategy')]
interface MoneyOperationInterface
{
    public function supports(string $operation): bool;

    public function handle(OperationInfoDto $dto): string;
}
