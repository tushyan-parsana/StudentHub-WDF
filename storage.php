<?php
declare(strict_types=1);

function readJsonRecords(string $filePath): array
{
    if (!is_file($filePath)) {
        return [];
    }

    $contents = file_get_contents($filePath);
    if ($contents === false || trim($contents) === '') {
        return [];
    }

    $records = json_decode($contents, true);
    if (!is_array($records)) {
        throw new RuntimeException('Stored data is not valid JSON.');
    }

    return $records;
}

function appendJsonRecord(string $filePath, array $record): void
{
    $directory = dirname($filePath);
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Storage directory could not be created.');
    }

    $handle = fopen($filePath, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Storage file could not be opened.');
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('Storage file could not be locked.');
        }

        $contents = stream_get_contents($handle);
        $records = $contents === false || trim($contents) === ''
            ? []
            : json_decode($contents, true);

        if (!is_array($records)) {
            throw new RuntimeException('Stored data is not valid JSON.');
        }

        $records[] = $record;
        $json = json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        if (ftruncate($handle, 0) === false || rewind($handle) === false) {
            throw new RuntimeException('Storage file could not be prepared for writing.');
        }
        if (fwrite($handle, $json . PHP_EOL) === false) {
            throw new RuntimeException('Stored record could not be written.');
        }
        fflush($handle);
        flock($handle, LOCK_UN);
    } finally {
        fclose($handle);
    }
}
