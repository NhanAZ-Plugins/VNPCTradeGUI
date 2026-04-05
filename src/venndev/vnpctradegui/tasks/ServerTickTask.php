<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\tasks;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use venndev\vnpctradegui\data\ItemTrade;
use venndev\vnpctradegui\utils\TradeUtil;
use venndev\vnpctradegui\utils\TypeTradeMenu;
use venndev\vnpctradegui\VNPCTradeGUI;
use vennv\vapm\Async;
use vennv\vapm\FiberManager;
use vennv\vapm\Promise;
use Throwable;

final class ServerTickTask extends Task
{

    private static ?Async $promise = null;

    public function __construct(
        private readonly VNPCTradeGUI $plugin
    )
    {
        //TODO: Implement
    }

    /**
     * @throws Throwable
     */
    public function onRun(): void
    {
        if (self::$promise === null) {
            self::$promise = new Async(function (): void {
                try {
                    Async::await($this->processTrade());
                } catch (Throwable $e) {
                    $this->getPlugin()->getLogger()->error($e->getMessage());
                }
                self::$promise = null;
            });
        }
    }

    /**
     * @throws Throwable
     */
    private function processTrade(): Async
    {
        return new Async(function (): void {
            try {
                foreach ($this->getPlugin()->getServer()->getOnlinePlayers() as $player) {
                    Async::await($this->processClassicTrade($player));
                }
            } catch (Throwable $e) {
                $this->getPlugin()->getLogger()->error($e->getMessage());
            }
        });
    }

    /**
     * @throws Throwable
     */
    private function processClassicTrade(Player $player): Promise
    {
        return new Promise(function ($resolve, $reject) use ($player): void {
            try {
                $window = $player->getCurrentWindow();
                $data = $this->getPlugin()->getDataPlayerHandler()::getData($player);
                if ($data !== null && $data->isOpenTradeMenu() && $window !== null) {
                    if ($data->getTypeTrade() === TypeTradeMenu::CLASSIC) {
                        $slotItemReceive = [
                            15 => [10, 11, 12],
                            33 => [28, 29, 30],
                            51 => [46, 47, 48]
                        ];
                        foreach ($slotItemReceive as $slot => $slots) {
                            $itemsTrade = new ItemTrade();
                            $itemsTradeOffer = new ItemTrade();
                            $itemReceive = $window->getItem($slot - 9);

                            $itemsTradeOffer->setItemOutput($itemReceive);
                            $itemsTrade->setItemOutput($window->getItem($slot));

                            foreach ($slots as $slotOffer) {
                                $item = $window->getItem($slotOffer - 9);
                                $itemsTradeOffer->addItemOffer($item);
                                $itemsTrade->addItemOffer($window->getItem($slotOffer));
                                FiberManager::wait();
                            }

                            $resultEquals = $itemsTrade->getResultEquals($itemsTradeOffer);
                            if ($resultEquals["result"]) {
                                $itemResult = clone $itemReceive;
                                $itemResult = TradeUtil::checkAndRemoveTagBarrier($itemResult);
                                if (($itemNow = $window->getItem($slot))->isNull()) {
                                    $window->setItem($slot, $itemResult);
                                    foreach ($slots as $slotOffer) {
                                        $itemsA = [10, 28, 46];
                                        $itemsB = [11, 29, 47];
                                        $itemsC = [12, 30, 48];
                                        if (in_array($slotOffer, $itemsA)) {
                                            $window->setItem($slotOffer, $resultEquals["itemA"]);
                                        } elseif (in_array($slotOffer, $itemsB)) {
                                            $window->setItem($slotOffer, $resultEquals["itemB"]);
                                        } elseif (in_array($slotOffer, $itemsC)) {
                                            $window->setItem($slotOffer, $resultEquals["itemC"]);
                                        }
                                        FiberManager::wait();
                                    }
                                }
                            }
                            FiberManager::wait();
                        }
                    }
                }
                $resolve();
            } catch (Throwable $e) {
                $reject($e);
            }
        });
    }

    public function getPlugin(): VNPCTradeGUI
    {
        return $this->plugin;
    }

}
