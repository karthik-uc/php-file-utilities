<?php

namespace DataUtils;

class DataFormatter
{

    private static function toDate($dateString)
    {
        $d = new \DateTime($dateString);
        return $d->format('Y-m-d');
    }

    private static function toBoolean($data)
    {
        return (bool)$data;
    }

    private static function toStandardDateTime($dateString)
    {
        $d = new \DateTime($dateString);
        return $d->format('Y-m-d H:i:s');
    }

    private static function toISODateTime($dateString)
    {
        $d = new \DateTime($dateString);
        return $d->format(DATE_RFC3339_EXTENDED);
    }

    private static function toString($string)
    {
        return (string)$string;
    }

    private static function toFloat($string)
    {
        return (float)$string;
    }

    private static function toInt($string)
    {
        return (int)$string;
    }

    public static function formatDataType(array $data, array $typeMap): array
    {
        foreach ($typeMap as $fieldName => $datatype) {
            $value = $data[$fieldName];

            if (is_null($value)) {
                continue;
            }

            switch ($datatype) {
                case 'float':
                    $data[$fieldName] = self::toFloat($value);
                    break;
                case 'int':
                    $data[$fieldName] = self::toInt($value);
                    break;
                case 'date':
                    $data[$fieldName] = self::toDate($value);
                    break;
                case 'datetimeISO':
                    $data[$fieldName] = self::toISODateTime($value);
                    break;
                case 'datetime':
                    $data[$fieldName] = self::toStandardDateTime($value);
                    break;
                case 'bool':
                    $data[$fieldName] = self::toBoolean($value);
                    break;
                default:
                    $data[$fieldName] = self::toString($value);
                    break;
            }
        }
        return $data;
    }
}
