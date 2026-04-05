<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\handler;

use pocketmine\player\Player;
use venndev\vnpctradegui\player\VPlayer;

final class DataPlayerHandler
{

    private static array $data = [];

    public static function setData(Player $player, VPlayer $vPlayer): void
    {
        if (!isset(self::$data[$player->getName()])) self::$data[$player->getName()] = $vPlayer;
    }

    public static function removeData(Player $player): void
    {
        if (isset(self::$data[$player->getName()])) unset(self::$data[$player->getName()]);
    }

    public static function getData(Player $player): ?VPlayer
    {
        return self::$data[$player->getName()] ?? null;
    }

}
