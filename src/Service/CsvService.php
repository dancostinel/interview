<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\AbstractFileService;
use App\Dto\OperationInfoDto;
use App\Exception\ReadCsvException;
use App\Exception\ReadFileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvService extends AbstractFileService
{
    public function __construct(private FeeService $feeService)
    {
    }

    /**
     * @throws ReadFileException
     */
    public function processRequest(UploadedFile $csvFile): array
    {
        $csvParsedLineGenerator = $this->parseFile($csvFile);
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
    public function readFile(string $filename, string $delimiter = ';'): \Generator
    {
        $handle = fopen($filename, "r");

        if (false === $handle) {
            throw new ReadCsvException('Unable to open file to read');
        }

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            yield $data;
        }

        fclose($handle);
    }

    /**
     * @throws ReadCsvException
     */
    public function parseFileLine(mixed $csvLine): OperationInfoDto
    {
        $line = reset($csvLine);
        if (!is_array($csvLine) || empty($csvLine) || !$line) {
            throw new ReadCsvException('Missing line');
        }

        $parsedCsvLine = explode(',', $line);
        if (empty($parsedCsvLine)) {
            throw new ReadCsvException('Empty CSV line data');
        }

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
