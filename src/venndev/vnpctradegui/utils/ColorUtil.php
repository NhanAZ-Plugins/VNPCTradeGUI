<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\utils;

final class ColorUtil
{

    public static function removeColor(string $message): string
    {
        return preg_replace("/(§|&)[0-9a-fk-or]/", "", $message);
    }

}
