<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\provider;

use pocketmine\utils\Config;

final class ConfigManager
{

    private const NAME_FOLDER_TRADE = "trades";
    private static string $path;

    public static function init(string $path): void
    {
        self::$path = $path;
        if (!is_dir($path . self::NAME_FOLDER_TRADE)) @mkdir($path . self::NAME_FOLDER_TRADE);
    }

    public static function getTradeConfig(string $name): ?Config
    {
        $pathName = self::$path . self::NAME_FOLDER_TRADE . DIRECTORY_SEPARATOR . $name . ".yml";
        return !file_exists($pathName) ? null : new Config($pathName, Config::YAML);
    }

    public static function createTradeConfig(string $name): Config
    {
        $pathName = self::$path . self::NAME_FOLDER_TRADE . DIRECTORY_SEPARATOR . $name . ".yml";
        return new Config($pathName, Config::YAML);
    }

    public static function deleteTradeConfig(string $name): void
    {
        $pathName = self::$path . self::NAME_FOLDER_TRADE . DIRECTORY_SEPARATOR . $name . ".yml";
        if (file_exists($pathName)) @unlink($pathName);
    }

}
