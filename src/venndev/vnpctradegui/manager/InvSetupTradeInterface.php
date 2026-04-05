<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\manager;

use pocketmine\inventory\Inventory;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vnpctradegui\data\TradeStructData;
use venndev\vnpctradegui\utils\ItemUtil;
use venndev\vnpctradegui\utils\MathUtil;
use venndev\vnpctradegui\utils\TypeTradeMenu;
use venndev\vnpctradegui\VNPCTradeGUI;
use vennv\vapm\FiberManager;
use vennv\vapm\Promise;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use vennv\vapm\System;
use Throwable;

final class InvSetupTradeInterface
{

    private Promise $resultSendMenu;
    private Promise $closeResult;

    /**
     * @throws Throwable
     */
    public function __construct(Player $player, string $nameMenu)
    {
        $data = VNPCTradeGUI::getConfigManager()::getTradeConfig($nameMenu);
        if ($data === null) {
            $player->sendMessage(TextFormat::RED . "This is not a valid trade menu.");
        } else {
            $this->resultSendMenu = new Promise(function ($resolve, $reject) use ($player, $data): void {
                try {
                    $data = TradeStructData::fromArray($data->getAll());

                    $itemNextPage = ItemUtil::decodeItem($data->getItemNextPage());
                    $itemPrevious = ItemUtil::decodeItem($data->getItemPrevious());

                    $itemNextPage->getNamedTag()->setString("nextPage", "nextPage");
                    $itemPrevious->getNamedTag()->setString("previous", "previous");

                    $player->getInventory()->addItem($itemNextPage);
                    $player->getInventory()->addItem($itemPrevious);

                    $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
                    $inventory = $menu->getInventory();

                    $menu->setName("Setup menu for: " . $data->getName());

                    $barrier = ItemUtil::getItem("barrier");
                    $barrier->getNamedTag()->setString("barrier", "barrier");

                    $backgroundItems = $data->getItemsBackGround();
                    foreach ($backgroundItems as $slot => $item) {
                        $inventory->setItem($slot, ItemUtil::decodeItem($item));
                        FiberManager::wait();
                    }

                    $itemNextPage = ItemUtil::decodeItem($data->getItemNextPage());
                    $itemPrevious = ItemUtil::decodeItem($data->getItemPrevious());
                    $itemNextPage->getNamedTag()->setString("nextPage", "nextPage");
                    $itemPrevious->getNamedTag()->setString("previous", "previous");
                    $inventory->setItem($data->getSlotNextPage(), $itemNextPage);
                    $inventory->setItem($data->getSlotPrevious(), $itemPrevious);

                    if ($data->getTypeTrade() === TypeTradeMenu::CLASSIC) {
                        $maxMatrix = [46, 47, 48, 51];
                        $minMatrix = [1, 2, 3, 6];

                        foreach ($minMatrix as $case => $slot) {
                            for ($i = $slot; $i <= $maxMatrix[$case]; $i += 9) {
                                $inventory->setItem($i, $barrier);
                                FiberManager::wait();
                            }
                            FiberManager::wait();
                        }
                    }

                    if ($data->getTypeTrade() === TypeTradeMenu::MODERN) {
                        $slotsTrade = MathUtil::getSlotsInArea(10, 43, 4, 7, 9);
                        foreach ($slotsTrade as $slot) {
                            $inventory->setItem($slot, $barrier);
                            FiberManager::wait();
                        }
                    }

                    $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
                        if ($transaction->getItemClicked()->getNamedTag()->getTag("barrier")) return $transaction->discard();
                        return $transaction->continue();
                    });

                    $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($menu, $data): void {
                        $player->sendMessage(TextFormat::GOLD . "Wait a moment, saving trade menu...");
                        $this->closeResult = new Promise(function ($resolve, $reject) use ($player, $inventory, $menu, $data): void {
                            try {
                                $haveNextPage = $havePrevious = false;
                                for ($i = 0; $i <= 53; $i++) {
                                    $item = $inventory->getItem($i);
                                    if ($item->getNamedTag()->getTag("nextPage")) {
                                        $haveNextPage = true;
                                        $data->setItemNextPage(ItemUtil::encodeItem($item));
                                        $data->setSlotNextPage($i);
                                    } elseif ($item->getNamedTag()->getTag("previous")) {
                                        $havePrevious = true;
                                        $data->setItemPrevious(ItemUtil::encodeItem($item));
                                        $data->setSlotPrevious($i);
                                    } elseif (!$item->getNamedTag()->getTag("barrier")) {
                                        !$item->isNull() ? $data->setItemBackGround($i, ItemUtil::encodeItem($item)) : $data->removeItemBackGround($i);
                                    }
                                    FiberManager::wait();
                                }
                                if (!$haveNextPage || !$havePrevious) {
                                    $player->sendMessage(TextFormat::RED . "You must have a next page and previous page item.");
                                    $reject(System::setTimeout(function () use ($player, $menu) {
                                        $menu->send($player);
                                    }, 1000));
                                } else {
                                    $data->save();
                                    $player->sendMessage(TextFormat::GREEN . "Trade menu saved.");
                                    $resolve();
                                }
                            } catch (Throwable $e) {
                                VNPCTradeGUI::getInstance()->getLogger()->error($e->getMessage());
                                $reject($e);
                            }
                        });
                    });

                    $menu->send($player);
                    $resolve($menu);
                } catch (Throwable $e) {
                    VNPCTradeGUI::getInstance()->getLogger()->error($e->getMessage());
                    $reject($e);
                }
            });
        }
    }

    public function getResultSendMenu(): Promise
    {
        return $this->resultSendMenu;
    }

    public function getCloseResult(): Promise
    {
        return $this->closeResult;
    }

}
