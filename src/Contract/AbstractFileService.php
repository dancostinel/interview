<?php

declare(strict_types=1);

namespace App\Contract;

use App\Exception\ReadFileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractFileService
{
    abstract public function readFile(string $filename, string $delimiter = ';'): \Generator;
    abstract public function parseFileLine(mixed $csvLine): object;

    /**
     * @throws ReadFileException
     */
    public function parseFile(UploadedFile $file): \Generator
    {
        $generator = $this->readFile($file->getPathname());
        while ($generator->valid()) {
            yield $this->parseFileLine($generator->current());
            $generator->next();
        }
    }
}
