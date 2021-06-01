<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Service;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

class ArrayFlattenService
{
    public static function flattenArray(array $array, string $namespace = ''): array
    {
        $flattenedArray = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                self::flattenArray($value, $namespace . '.' . $key);
            }

            $flattenedArray[$namespace . '.' . $key] = $value;
        }

        return $flattenedArray;
    }
}
