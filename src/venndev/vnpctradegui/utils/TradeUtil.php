<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\utils;

use pocketmine\item\Item;
use venndev\vnpctradegui\data\TradeStructData;
use venndev\vnpctradegui\VNPCTradeGUI;

final class TradeUtil
{

    public static function checkAndRemoveTagBarrier(Item $item): Item
    {
        if ($item->getNamedTag()->getTag("barrier") !== null) $item->getNamedTag()->removeTag("barrier");
        return $item;
    }

    public static function getItemNextPageAndPrevPage(TradeStructData $data, int $page = 1): array
    {
        $replaces = [
            "%page%" => $page,
            "%max%" => $data->getMaxListPageItemsTrade(3)
        ];
        $config = VNPCTradeGUI::getInstance()->getConfig();
        $itemNextPageName = $config->getNested("item-next-page.name", "");
        $itemPreviousName = $config->getNested("item-previous-page.name", "");
        $itemNextPageLore = $config->getNested("item-next-page.lore", []);
        $itemPreviousLore = $config->getNested("item-previous-page.lore", []);
        $itemNextPage = ItemUtil::decodeItem($data->getItemNextPage());
        $itemPrevious = ItemUtil::decodeItem($data->getItemPrevious());
        $itemNextPage->getNamedTag()->setString("nextPage", "nextPage");
        $itemPrevious->getNamedTag()->setString("previous", "previous");
        $itemNextPage->setCustomName(MessageUtil::process($itemNextPageName, $replaces));
        $itemPrevious->setCustomName(MessageUtil::process($itemPreviousName, $replaces));
        $itemNextPage->setLore(array_map(function (string $line) use ($replaces): string {
            return MessageUtil::process($line, $replaces);
        }, $itemNextPageLore));
        $itemPrevious->setLore(array_map(function (string $line) use ($replaces): string {
            return MessageUtil::process($line, $replaces);
        }, $itemPreviousLore));
        return [$itemNextPage, $itemPrevious];
    }

}
