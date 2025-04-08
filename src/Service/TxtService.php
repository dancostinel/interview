<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\AbstractFileService;
use App\Dto\CommissionInfoDto;
use App\Exception\ApiException;
use App\Exception\ReadFileException;
use App\Exception\ReadTxtException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TxtService extends AbstractFileService
{
    public function __construct(private CommissionService $commissionService)
    {
    }

    /**
     * @throws ReadFileException
     * @throws ApiException
     */
    public function processRequest(UploadedFile $txtFile): array
    {
        $txtParsedLineGenerator = $this->parseFile($txtFile);
        while ($txtParsedLineGenerator->valid()) {
            /** @var CommissionInfoDto $dto */
            $dto = $txtParsedLineGenerator->current();
            $commissionValues[] = $this->commissionService->getCommissionValue($dto);
            $txtParsedLineGenerator->next();
        }

        return $commissionValues ?? [];
    }

    /**
     * @throws ReadTxtException
     */
    public function readFile(string $filename, string $delimeter = "\n"): \Generator
    {
        $handle = fopen($filename, "r");

        if (false === $handle) {
            throw new ReadTxtException('Unable to open file to read');
        }

        while (($data = fgets($handle)) !== false) {
            yield $data;
        }

        fclose($handle);
    }

    /**
     * @throws ReadTxtException
     */
    public function parseFileLine(mixed $line): CommissionInfoDto
    {
        if (empty($line)) {
            throw new ReadTxtException('Missing line');
        }

        $parsedCsvLine = json_decode($line, true);

        if (empty($parsedCsvLine)) {
            throw new ReadTxtException('Empty TXT line data');
        }

        return new CommissionInfoDto(
            $parsedCsvLine['bin'] ?? '',
            $parsedCsvLine['amount'] ?? '',
            $parsedCsvLine['currency'] ?? '',
        );
    }
}
