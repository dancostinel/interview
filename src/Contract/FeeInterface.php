<?php

declare(strict_types=1);

namespace App\Contract;

use App\Dto\OperationInfoDto;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'fee_strategy')]
interface FeeInterface
{
    public function supports(string $currency): bool;

    public function getFee(OperationInfoDto $dto): string;
}
