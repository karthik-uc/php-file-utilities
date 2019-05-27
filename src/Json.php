<?php

class Json
{
    public static function load(string $filename, bool $returnIsArray = false)
    {
        $contents = \file_get_contents($filename);
        return \json_decode($contents, $returnIsArray);
    }


    public static function dump(string $filename, $data, $pretty_print = false)
    {
        $options = $pretty_print ? JSON_PRETTY_PRINT : 0;
        $string = \json_encode($data, $options);
        return \file_put_contents($filename, $string);
    }


    public static function loadJsonLine(string $filename, bool $returnIsArray = false)
    {
        if (!file_exists($filename)) {
            throw new \Exception("File not found: {$filename}");
        }

        $f = @fopen($filename, 'r');
        while (($data = fgets($f)) !== false) {
            yield \json_decode($data, $returnIsArray);
        }
        fclose($f);
    }


    public static function dumpJsonLine(string $filename, array $data)
    {
        if (file_exists($filename)) {
            throw new \Exception("Error: File Already Exists with name {$filename}\n");
        }

        if (!is_array(json_decode(json_encode($data)))) {
            throw new \Exception('Data is not an array');
        }

        foreach ($data as $lineArray) {
            \file_put_contents($filename, \json_encode($lineArray) . "\n", FILE_APPEND);
        }
    }
}
