<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\OperationInfoDto;

class DateIntervalService
{
    public function compute(OperationInfoDto $dto): string
    {
        $startDayOfWeek = $dto->getOperationDate()
            ->modify('monday this week')
            ->format('Y-m-d');

        $endDayOfWeek = $dto->getOperationDate()
            ->modify('sunday this week')
            ->format('Y-m-d');

        return $dto->getUserId() . '_' . $startDayOfWeek . '_' . $endDayOfWeek;
    }
}
