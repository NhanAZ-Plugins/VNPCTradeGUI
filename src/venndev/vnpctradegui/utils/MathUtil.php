<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\utils;

use Generator;

final class MathUtil
{

    public static function getSlotsInArea(
        int $min,
        int $max,
        int $rowArea,
        int $colArea,
        int $area
    ): Generator
    {
        $isWithinArea = function (int $n) use ($rowArea, $colArea, $area): bool {
            $row = intval($n / $area);
            $col = $n % $area;
            return ($row >= 1 && $row <= $rowArea && $col >= 1 && $col <= $colArea);
        };
        for ($n = $min; $n <= $max; $n++) if ($isWithinArea($n)) yield $n;
    }

}
