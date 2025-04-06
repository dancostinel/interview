<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\OperationInfoDto;
use App\Exception\ReadCsvException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvService
{
    public function __construct(private FeeService $feeService)
    {
    }

    /**
     * @throws ReadCsvException
     */
    public function processRequest(UploadedFile $csvFile): array
    {
        $csvParsedLineGenerator = $this->parseCsvFile($csvFile);
        while ($csvParsedLineGenerator->valid()) {
            /** @var OperationInfoDto $dto */
            $dto = $csvParsedLineGenerator->current();
            $feeValues[] = $this->feeService->getFeeValue($dto);
            $csvParsedLineGenerator->next();
        }

        return $feeValues ?? [];
    }

    /**
     * @throws ReadCsvException
     */
    private function parseCsvFile(UploadedFile $csvFile): \Generator
    {
        $generator = $this->readCsvFile($csvFile->getPathname());
        while ($generator->valid()) {
            yield $this->parseCsvLine($generator->current());
            $generator->next();
        }
    }

    /**
     * @throws ReadCsvException
     */
    private function readCsvFile(string $filename, string $delimeter = ';'): \Generator
    {
        $handle = fopen($filename, "r");

        if (false === $handle) {
            throw new ReadCsvException('Unable to open csv file to read');
        }

        while (($data = fgetcsv($handle, 0, $delimeter)) !== false) {
            yield $data;
        }

        fclose($handle);
    }

    /**
     * @throws ReadCsvException
     */
    private function parseCsvLine(mixed $csvLine): OperationInfoDto
    {
        $line = reset($csvLine);
        if (!is_array($csvLine) || empty($csvLine) || !$line) {
            throw new ReadCsvException('Empty CSV content line');
        }

        $parsedCsvLine = explode(',', $line);
        if (empty($parsedCsvLine)) {
            throw new ReadCsvException('Empty CSV line data');
        }

        return $this->getOperationInfoDto($parsedCsvLine);
    }

    private function getOperationInfoDto(array $parsedCsvLine): OperationInfoDto
    {
        return new OperationInfoDto(
            $parsedCsvLine[0],
            $parsedCsvLine[1],
            $parsedCsvLine[2],
            $parsedCsvLine[3],
            $parsedCsvLine[4],
            $parsedCsvLine[5]
        );
    }
}
