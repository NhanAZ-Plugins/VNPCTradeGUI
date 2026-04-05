<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\manager;

use pocketmine\inventory\Inventory;
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

final class InvRemoveTrade
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

                    $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
                    $inventory = $menu->getInventory();

                    $menu->setName("Remove trade: " . $data->getName());

                    $slotsTrade = iterator_to_array(MathUtil::getSlotsInArea(10, 43, 4, 7, 9));
                    for ($i = 0; $i < 54; $i++) {
                        if (!in_array($i, $slotsTrade)) {
                            $barrier = ItemUtil::getItem("barrier");
                            $barrier->setCustomName(TextFormat::RESET . TextFormat::GRAY);
                            $barrier->getNamedTag()->setString("barrier", "barrier");
                            $inventory->setItem($i, $barrier);
                        }
                    }

                    $itemsForListPage = TradeUtil::getItemNextPageAndPrevPage($data, $page);
                    $inventory->setItem($data->getSlotNextPage(), $itemsForListPage[0]);
                    $inventory->setItem($data->getSlotPrevious(), $itemsForListPage[1]);

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
                                $loreAddition = [
                                    TextFormat::RESET . TextFormat::GRAY,
                                    TextFormat::RESET . TextFormat::GOLD . "Click to remove!",
                                    TextFormat::RESET . TextFormat::GRAY,
                                    TextFormat::RESET . TextFormat::GRAY . "Item required:"
                                ];
                                if ($itemA !== null) $loreAddition[] = TextFormat::RESET . TextFormat::WHITE . $itemA->getName() . TextFormat::GRAY . " x" . TextFormat::WHITE . $itemA->getCount();
                                if ($itemB !== null) $loreAddition[] = TextFormat::RESET . TextFormat::WHITE . $itemB->getName() . TextFormat::GRAY . " x" . TextFormat::WHITE . $itemB->getCount();
                                if ($itemC !== null) $loreAddition[] = TextFormat::RESET . TextFormat::WHITE . $itemC->getName() . TextFormat::GRAY . " x" . TextFormat::WHITE . $itemC->getCount();
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
                            new Async(function () use ($player, $itemClicked, $data): void {
                                $itemATag = $itemClicked->getNamedTag()->getTag("itemA");
                                $itemBTag = $itemClicked->getNamedTag()->getTag("itemB");
                                $itemCTag = $itemClicked->getNamedTag()->getTag("itemC");
                                $itemA = $itemATag !== null ? ItemUtil::decodeItem($itemATag->getValue()) : null;
                                $itemB = $itemBTag !== null ? ItemUtil::decodeItem($itemBTag->getValue()) : null;
                                $itemC = $itemCTag !== null ? ItemUtil::decodeItem($itemCTag->getValue()) : null;

                                $itemOutput = ItemUtil::decodeItem($itemClicked->getNamedTag()->getString("itemOutput"));
                                if (Async::await($data->removeItemTrade($itemA, $itemB, $itemC, $itemOutput))) {
                                    $player->getCurrentWindow()->removeItem($itemClicked);
                                } else {
                                    VNPCTradeGUI::getInstance()->getLogger()->error("Error removing item trade.");
                                }
                            });
                            return $transaction->discard();
                        }
                        return $transaction->continue();
                    });

                    $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($dataPlayer): void {
                        $this->closeResult = new Promise(function ($resolve, $reject) use ($dataPlayer, $player, $inventory): void {
                            try {
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
