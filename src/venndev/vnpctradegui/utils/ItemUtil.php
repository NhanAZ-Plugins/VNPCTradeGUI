<?php
namespace venndev\vnpctradegui\utils;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;

final class ItemUtil
{

    public static function getItem(string $item): Item
    {
        return StringToItemParser::getInstance()->parse($item);
    }

    public static function encodeItem(Item $item): string
    {
        return base64_encode(gzcompress(self::itemToJson($item)));
    }

    public static function decodeItem(string $item): Item
    {
        return self::jsonToItem(gzuncompress(base64_decode($item)));
    }

    public static function itemToJson(Item $item): string
    {
        $cloneItem = clone $item;
        return base64_encode(serialize($cloneItem->nbtSerialize()));
    }

    public static function jsonToItem(string $json): Item
    {
        return Item::nbtDeserialize(unserialize(base64_decode($json)));
    }

}
