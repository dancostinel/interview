<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ReadCsvException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ParseFileService
{
    /**
     * @throws ReadCsvException
     */
    public function parseTxtFile(UploadedFile $csvFile): \Generator
    {
        $generator = $this->readCsvFile($csvFile->getPathname());
        while ($generator->valid()) {
            yield $this->parseCsvLine($generator->current());
            $generator->next();
        }
    }
}
