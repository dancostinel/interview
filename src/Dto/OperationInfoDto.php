<?php

declare(strict_types=1);

namespace App\Dto;

class OperationInfoDto
{
    private string $operationDate;
    private string $userId;
    private string $userType;
    private string $operationType;
    private string $amount;
    private string $currency;

    public function __construct(
        $operationDate,
        $userId,
        $userType,
        $operationType,
        $amount,
        $currency
    )
    {
        $this->operationDate = $operationDate;
        $this->userId = $userId;
        $this->userType = $userType;
        $this->operationType = $operationType;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getOperationDate(): \DateTime
    {
        return new \DateTime($this->operationDate);
    }

    public function getUserId(): int
    {
        return (int) $this->userId;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }

    public function getAmount(): float
    {
        return (float) $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function toJson(): string
    {
        return json_encode([
            'date' => $this->operationDate,
            'user-id' => $this->userId,
            'user-type' => $this->userType,
            'operation-type' => $this->operationType,
            'amount' => $this->amount,
            'currency' => $this->currency,
        ]);
    }
}
