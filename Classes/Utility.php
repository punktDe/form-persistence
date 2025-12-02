<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence;

class Utility
{
    public static function sortDimensionValueArrayAndReturnDimensionsHash(array &$dimensionValues)
    {
        foreach ($dimensionValues as &$values) {
            sort($values);
        }
        ksort($dimensionValues);

        return md5(json_encode($dimensionValues));
    }
}
