<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\utils;

use pocketmine\utils\TextFormat;

final class MessageUtil
{

    public static function process(string $message, array $replaces = []): string
    {
        return TextFormat::colorize(str_replace(array_keys($replaces), array_values($replaces), $message));
    }

}
