<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\manager;

use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\EndermanTeleportSound;
use venndev\vnpctradegui\data\ItemTrade;
use venndev\vnpctradegui\data\TradeStructData;
use venndev\vnpctradegui\utils\ItemUtil;
use venndev\vnpctradegui\utils\MathUtil;
use venndev\vnpctradegui\utils\MessageUtil;
use venndev\vnpctradegui\utils\TradeUtil;
use venndev\vnpctradegui\utils\TypeTradeMenu;
use venndev\vnpctradegui\VNPCTradeGUI;
use vennv\vapm\Async;
use vennv\vapm\FiberManager;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use vennv\vapm\Promise;
use Throwable;

final class InvTrade
{

    private Async $resultSendMenu;

    private Promise $closeResult;

    /**
     * @throws Throwable
     */
    public function __construct(Player $player, string $nameMenu, int $page = 1, bool $justGetData = false)
    {
        $data = VNPCTradeGUI::getConfigManager()::getTradeConfig($nameMenu);
        if ($data === null) {
            $player->sendMessage(TextFormat::RED . "This is not a valid trade menu.");
        } else {
            $this->resultSendMenu = new Async(function () use ($player, $data, $page, $nameMenu, $justGetData): bool|InvMenu {
                try {
                    $dataPlayer = VNPCTradeGUI::getInstance()::getDataPlayerHandler()::getData($player);
                    if ($dataPlayer !== null) {
                        if ($dataPlayer->isOpenTradeMenu() && !$justGetData) {
                            $player->sendMessage(TextFormat::RED . "You already have a trade menu open.");
                            return false;
                        }
                    }

                    $data = TradeStructData::fromArray($data->getAll());
                    $dataPlayer->openTradeMenu($data->getTypeTrade());
                    $typeTrade = $data->getTypeTrade();

                    $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
                    $inventory = $menu->getInventory();

                    $menu->setName($data->getName());

                    $backgroundItems = $data->getItemsBackGround();
                    foreach ($backgroundItems as $slot => $item) {
                        $barrier = ItemUtil::decodeItem($item);
                        $barrier->setCustomName(TextFormat::RESET . TextFormat::GRAY);
                        $barrier->getNamedTag()->setString("barrier", "barrier");
                        $inventory->setItem($slot, $barrier);
                        FiberManager::wait();
                    }

                    $replaces = [
                        "%page%" => $page,
                        "%max%" => $data->getMaxListPageItemsTrade(3)
                    ];

                    $itemsForListPage = TradeUtil::getItemNextPageAndPrevPage($data, $page);
                    $inventory->setItem($data->getSlotNextPage(), $itemsForListPage[0]);
                    $inventory->setItem($data->getSlotPrevious(), $itemsForListPage[1]);

                    $slotItemReceive = [
                        15 => [10, 11, 12],
                        33 => [28, 29, 30],
                        51 => [46, 47, 48]
                    ];

                    if ($typeTrade === TypeTradeMenu::CLASSIC) {
                        $i = 0;
                        $itemsTradeInPage = Async::await($data->getListPageItemsTrade(3, $page));
                        foreach ($slotItemReceive as $slotReceive => $slotsItem) {
                            if (isset($itemsTradeInPage[$i])) {
                                $itemTrade = $itemsTradeInPage[$i];
                                $itemTrade = ItemTrade::fromArray($itemTrade);

                                if (($itemOutput = $itemTrade->getItemOutput()) !== null) {
                                    $itemOutput->getNamedTag()->setString("barrier", "barrier");
                                    $inventory->setItem($slotReceive - 9, $itemOutput);
                                }

                                $itemOffer = $itemTrade->getItemOffer();
                                foreach ($slotsItem as $slot) {
                                    foreach ($itemOffer as $case => $item) {
                                        if ($item !== null) {
                                            $item->getNamedTag()->setString("barrier", "barrier");
                                            $inventory->setItem($slot - 9, $item);
                                        }
                                        unset($itemOffer[$case]);
                                        break;
                                    }
                                }
                                $i++;
                            } else {
                                break;
                            }
                        }

                        $maxMatrix = [46, 47, 48, 51];
                        $minMatrix = [1, 2, 3, 6];

                        $slotsTrade = [];
                        foreach ($minMatrix as $case => $slot) {
                            for ($i = $slot; $i <= $maxMatrix[$case]; $i += 9) $slotsTrade[] = $i;
                        }

                        $mergedArray = call_user_func_array('array_merge', array_values($slotItemReceive));
                        $mergedArray = array_merge(array_keys($slotItemReceive), $mergedArray);
                        for ($i = 0; $i < 54; $i++) {
                            if (!in_array($i, $mergedArray) && in_array($i, $slotsTrade) && $inventory->getItem($i)->isNull()) {
                                $itemSpace = $data->getItemSpace();
                                $itemSpace->getNamedTag()->setString("barrier", "barrier");
                                $inventory->setItem($i, $itemSpace);
                            }
                        }
                    }

                    $config = VNPCTradeGUI::getInstance()->getConfig();
                    if ($typeTrade === TypeTradeMenu::MODERN) {
                        $i = 0;
                        $slotsTrade = MathUtil::getSlotsInArea(10, 43, 4, 7, 9);
                        $itemsTradeInPage = Async::await($data->getListPageItemsTrade(28, $page));
                        foreach ($slotsTrade as $slot) {
                            if (isset($itemsTradeInPage[$i])) {
                                $itemTrade = $itemsTradeInPage[$i];
                                $itemTrade = ItemTrade::fromArray($itemTrade);
                                if (($itemOutput = clone $itemTrade->getItemOutput()) !== null) {
                                    $itemA = $itemTrade->getItemA();
                                    $itemB = $itemTrade->getItemB();
                                    $itemC = $itemTrade->getItemC();
                                    $loreAdditionConfig = $config->getNested("lore-addition-item-trade", []);
                                    $functionCheckItem = function (string $key, Item|null $item, array &$replaces): void {
                                        if ($item !== null) {
                                            $replaces["%item" . $key . "%"] = $item->getName();
                                            $replaces["%amount" . $key . "%"] = $item->getCount();
                                        } else {
                                            $replaces["%item" . $key . "%"] = "";
                                            $replaces["%amount" . $key . "%"] = 0;
                                        }
                                    };
                                    $functionCheckItem("A", $itemA, $replaces);
                                    $functionCheckItem("B", $itemB, $replaces);
                                    $functionCheckItem("C", $itemC, $replaces);
                                    $loreAddition = array_map(function (string $line) use ($replaces): string {
                                        return MessageUtil::process($line, $replaces);
                                    }, $loreAdditionConfig);
                                    $itemOutput->setLore(array_merge($itemOutput->getLore(), $loreAddition));
                                    if ($itemA !== null) $itemOutput->getNamedTag()->setString("itemA", ItemUtil::encodeItem($itemA));
                                    if ($itemB !== null) $itemOutput->getNamedTag()->setString("itemB", ItemUtil::encodeItem($itemB));
                                    if ($itemC !== null) $itemOutput->getNamedTag()->setString("itemC", ItemUtil::encodeItem($itemC));
                                    $itemOutput->getNamedTag()->setString("itemOutput", ItemUtil::encodeItem($itemTrade->getItemOutput()));
                                    $inventory->setItem($slot, $itemOutput);
                                }
                            }
                            $i++;
                            FiberManager::wait();
                        }
                    }

                    if ($justGetData) return $menu;

                    $menu->setListener(function (InvMenuTransaction $transaction) use ($data, &$page, $nameMenu): InvMenuTransactionResult {
                        $player = $transaction->getPlayer();
                        $itemClicked = $transaction->getItemClicked();
                        if ($itemClicked->getNamedTag()->getTag("barrier") !== null) return $transaction->discard();
                        if ($itemClicked->getNamedTag()->getTag("nextPage") !== null) {
                            if ($page < $data->getMaxListPageItemsTrade(3)) {
                                $page++;
                                new Async(function () use ($player, $nameMenu, $page): void {
                                    $class = new self($player, $nameMenu, $page, true);
                                    /** @var InvMenu $data */
                                    $data = Async::await($class->getResultSendMenu());
                                    $player->getCurrentWindow()->setContents($data->getInventory()->getContents());
                                });
                            }
                            return $transaction->discard();
                        } elseif ($itemClicked->getNamedTag()->getTag("previous") !== null) {
                            if ($page > 1) {
                                $page--;
                                new Async(function () use ($player, $nameMenu, $page): void {
                                    $class = new self($player, $nameMenu, $page, true);
                                    /** @var InvMenu $data */
                                    $data = Async::await($class->getResultSendMenu());
                                    $player->getCurrentWindow()->setContents($data->getInventory()->getContents());
                                });
                            }
                            return $transaction->discard();
                        } elseif ($itemClicked->getNamedTag()->getTag("itemOutput") !== null) {
                            new Async(function () use ($player, $itemClicked): void {
                                $itemATag = $itemClicked->getNamedTag()->getTag("itemA");
                                $itemBTag = $itemClicked->getNamedTag()->getTag("itemB");
                                $itemCTag = $itemClicked->getNamedTag()->getTag("itemC");
                                $itemA = $itemATag !== null ? ItemUtil::decodeItem($itemATag->getValue()) : null;
                                $itemB = $itemBTag !== null ? ItemUtil::decodeItem($itemBTag->getValue()) : null;
                                $itemC = $itemCTag !== null ? ItemUtil::decodeItem($itemCTag->getValue()) : null;
                                $itemOutput = ItemUtil::decodeItem($itemClicked->getNamedTag()->getString("itemOutput"));
                                $itemTrade = new ItemTrade();
                                $itemTrade->setItemA($itemA);
                                $itemTrade->setItemB($itemB);
                                $itemTrade->setItemC($itemC);
                                $itemTrade->setItemOutput($itemOutput);

                                $qualifiedA = $qualifiedB = $qualifiedC = false;
                                $itemA ?? $qualifiedA = true;
                                $itemB ?? $qualifiedB = true;
                                $itemC ?? $qualifiedC = true;
                                foreach ($player->getInventory()->getContents() as $slot => $item) {
                                    if ($itemA !== null && $item->equals($itemA) && $item->getCount() >= $itemA->getCount()) $qualifiedA = true;
                                    if ($itemB !== null && $item->equals($itemB) && $item->getCount() >= $itemB->getCount()) $qualifiedB = true;
                                    if ($itemC !== null && $item->equals($itemC) && $item->getCount() >= $itemC->getCount()) $qualifiedC = true;
                                    FiberManager::wait();
                                }

                                if ($qualifiedA && $qualifiedB && $qualifiedC) {
                                    if ($itemA !== null) $player->getInventory()->removeItem($itemA);
                                    if ($itemB !== null) $player->getInventory()->removeItem($itemB);
                                    if ($itemC !== null) $player->getInventory()->removeItem($itemC);
                                    $player->getInventory()->addItem($itemOutput);
                                } else {
                                    $player->getWorld()->addSound($player->getLocation()->asVector3(), new EndermanTeleportSound(), [$player]);
                                }
                            });

                            return $transaction->discard();
                        }
                        return $transaction->continue();
                    });

                    $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($dataPlayer, $slotItemReceive, $typeTrade): void {
                        $this->closeResult = new Promise(function ($resolve, $reject) use ($dataPlayer, $player, $inventory, $slotItemReceive, $typeTrade): void {
                            try {
                                // Classic trade
                                if ($typeTrade === TypeTradeMenu::CLASSIC) {
                                    foreach ($slotItemReceive as $slotsItem) {
                                        foreach ($slotsItem as $slot) {
                                            if (!$inventory->getItem($slot)->isNull()) $player->getInventory()->addItem($inventory->getItem($slot));
                                            FiberManager::wait();
                                        }
                                        FiberManager::wait();
                                    }
                                }
                                $dataPlayer->closeTradeMenu();
                                $resolve();
                            } catch (Throwable $e) {
                                VNPCTradeGUI::getInstance()->getLogger()->error($e->getMessage());
                                $reject($e);
                            }
                        });
                    });

                    $menu->send($player);
                } catch (Throwable $e) {
                    VNPCTradeGUI::getInstance()->getLogger()->error($e->getMessage());
                }

                return true;
            });
        }
    }

    public function getResultSendMenu(): Async
    {
        return $this->resultSendMenu;
    }

    public function getCloseResult(): Promise
    {
        return $this->closeResult;
    }

}
