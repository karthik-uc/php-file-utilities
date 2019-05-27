<?php

class Csv
{
    public static function loadIterator($filename, $hasHeaders = false)
    {
        if (empty($filename)) {
            throw new \Exception('Filename not provided', 400);
        }

        if (!file_exists($filename)) {
            throw new \Exception("File not found: {$filename}");
        }

        $f = \fopen($filename, 'r');
        if (!$f) {
            throw new \Exception("Couldn't open file {$filename}", 500);
        }

        if ($hasHeaders) {
            $csvHeaders = \fgetcsv($f);
        }

        while (($data = \fgetcsv($f)) !== false) {
            if ($hasHeaders) {
                yield array_combine($csvHeaders, $data);
            } else {
                yield $data;
            }
        }

        fclose($f);
    }


    public static function load(string $filename, bool $hasHeaders = false): array
    {
        $data = [];

        foreach (self::loadIterator($filename, $hasHeaders) as $csvRow) {
            $data[] = $csvRow;
        }
        return $data;
    }


    public static function getColumnByName(string $filename, string $columnName, string $indexKey = null): array
    {
        foreach (self::getColumnsByNames($filename, [$columnName], [$indexKey]) as $column) {
            return $column;
        }
    }

    public static function getColumnsByNames(string $filename, array $columnNames, array $indexKeys = [])
    {
        $response = [];
        $data = self::load($filename, true);
        foreach ($columnNames as $index => $columnName) {
            $response[] = array_column($data, $columnName, $indexKeys[$index]);
        }
        return $response;
    }

    public static function getSpecificColumns(string $filename, array $columnNames, array $headers = [], array $indexKeys = [])
    {
        $response = [];
        $data = self::getColumnsByNames($filename, $columnNames, $indexKeys);
        // transpose this
        foreach (array_keys($data[0]) as $key) {
            if (empty($headers)) {
                $response[] = array_column($data, $key);
            } else {
                if (count($headers) != count($data)) {
                    throw new \Exception('Number of headers are not matching with number of indexes');
                }
                $response[] = array_combine($headers, array_column($data, $key));
            }
        }
        return $response;
    }

    public static function getNthColumn(string $filename, int $columnNumber): array
    {
        return array_column(self::load($filename), $columnNumber);
    }

    public static function getNthRow(string $filename, int $rowNumber)
    {
        $lineNumber = 1;
        foreach (self::loadIterator($filename) as $csvRow) {
            if ($lineNumber == $rowNumber)
                return $csvRow;
            $lineNumber++;
        }
    }

    public static function alignColumnsWithHeaders(array $headers, \stdClass $rowData): array
    {
        $nCols = count($headers);
        $row = array_fill(0, $nCols, '');
        foreach ($rowData as $columnKey => $colummValue) {
            if (($index = array_search($columnKey, $headers)) === false) {
                continue;
            }

            $row[$index] = "{$colummValue}";
        }
        return $row;
    }


    public static function openCsvWriter(string $filename, array $headers = [])
    {
        if (empty($filename)) {
            throw new \Exception('Filename not provided', 400);
        }

        $f = \fopen($filename, 'w');
        if (!$f) {
            throw new \Exception("Couldn't open file {$filename}", 500);
        }

        if (empty($headers)) {
            return $f;
        }

        if (\fputcsv($f, $headers) === false) {
            fclose($f);
            throw new \Exception("Write failed to csv file {$filename}", 500);
        }
        return $f;
    }

    /**
     * Dumps data in csv
     *
     * @param string $filename  name of the file to dump data to
     * @param array  $rows      Array of objects container each row of csv where key of object is the column name and value is cell value.
     * @param array  $headers   column names of the csv header. If empty, first row is considered as data otherwise first row is header
     *
     * @return void
     *
     */
    public static function dump(string $filename, array $rows, array $headers = []): void
    {
        if (empty($rows)) {
            throw new \Exception("Data not sent for {$filename}", 400);
        }

        $f = self::openCsvWriter($filename, $headers);
        foreach ($rows as $row) {
            if (!empty($headers)) {
                $rowToWrite = self::alignColumnsWithHeaders($headers, (object)$row);
            } else {
                $rowToWrite = $row;
            }

            if (\fputcsv($f, $rowToWrite) === false) {
                throw new \Exception("Write failed to csv file {$filename}", 500);
            }
        }
        fclose($f);
    }
}
