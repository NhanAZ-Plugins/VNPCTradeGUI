<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\manager;

use pocketmine\inventory\Inventory;
use pocketmine\item\ItemTypeIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vnpctradegui\data\ItemTrade;
use venndev\vnpctradegui\data\TradeStructData;
use venndev\vnpctradegui\utils\ItemUtil;
use venndev\vnpctradegui\VNPCTradeGUI;
use vennv\vapm\FiberManager;
use vennv\vapm\Promise;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use vennv\vapm\System;
use Throwable;

final class InvAddTrade
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

                    $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
                    $inventory = $menu->getInventory();

                    $menu->setName("Add item trade for: " . $data->getName());

                    $barrier = ItemUtil::getItem("barrier");
                    $barrier->getNamedTag()->setString("barrier", "barrier");

                    $slotsItem = [10, 11, 12, 16];
                    for ($i = 0; $i <= 26; $i++) {
                        if (!in_array($i, $slotsItem)) $inventory->setItem($i, $barrier);
                        FiberManager::wait();
                    }

                    $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
                        if ($transaction->getItemClicked()->getNamedTag()->getTag("barrier")) return $transaction->discard();
                        return $transaction->continue();
                    });

                    $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($menu, $data, $slotsItem): void {
                        $player->sendMessage(TextFormat::GOLD . "Wait a moment, saving trade menu...");
                        $this->closeResult = new Promise(function ($resolve, $reject) use ($player, $inventory, $menu, $data, $slotsItem): void {
                            try {
                                $itemTrade = new ItemTrade();
                                $haveItemOffer = false;
                                $haveItemReceive = false;
                                foreach ($slotsItem as $slot) {
                                    if (!($item = $inventory->getItem($slot))->isNull()) {
                                        if ($slot === 16) {
                                            $haveItemReceive = true;
                                            $itemTrade->setItemOutput($item);
                                        } else {
                                            $haveItemOffer = true;
                                            $itemTrade->addItemOffer($item);
                                        }
                                    }
                                    FiberManager::wait();
                                }

                                if ($haveItemOffer && $haveItemReceive) {
                                    $player->sendMessage(TextFormat::GREEN . "New item trade has been added.");
                                    $data->addItemTrade($itemTrade);
                                    $data->save();
                                    $player->sendMessage(TextFormat::GREEN . "Trade menu has been saved.");
                                } else {
                                    $player->sendMessage(TextFormat::RED . "You must add at least one item to offer and one item to receive.");
                                }

                                $resolve($menu);
                            } catch (Throwable $e) {
                                VNPCTradeGUI::getInstance()->getLogger()->error($e->getMessage());
                                $reject($e);
                            }
                        });
                    });

                    $menu->send($player);
                    $resolve($menu);
                } catch (Throwable $e) {
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
