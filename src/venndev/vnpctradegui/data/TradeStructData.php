<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\data;

use pocketmine\item\Item;
use Throwable;
use venndev\vnpctradegui\utils\ColorUtil;
use venndev\vnpctradegui\utils\ItemUtil;
use venndev\vnpctradegui\utils\TypeTradeMenu;
use venndev\vnpctradegui\VNPCTradeGUI;
use vennv\vapm\Async;
use vennv\vapm\FiberManager;
use vennv\vapm\Promise;

final class TradeStructData
{

    private string $uid;

    /**
     * @var array<int, string(encoded_item)>
     */
    private array $itemsBackGround = [];

    /**
     * @var array<int, string(encoded_item)>
     */
    private array $itemsTrade = [];

    private Item $itemSpace;

    private int $slotNextPage = 53;
    private int $slotPrevious = 45;
    private string $itemNextPage;
    private string $itemPrevious;

    public function __construct(
        private string $name,
        private string $description,
        private string $typeTrade = TypeTradeMenu::CLASSIC
    )
    {
        $this->uid = uniqid($name);
        $this->itemNextPage = ItemUtil::encodeItem(ItemUtil::getItem("arrow"));
        $this->itemPrevious = ItemUtil::encodeItem(ItemUtil::getItem("arrow"));
        $this->itemSpace = ItemUtil::getItem("barrier");
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function getTypeTrade(): string
    {
        return $this->typeTrade;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getItemsBackGround(): array
    {
        return $this->itemsBackGround;
    }

    public function getItemsTrade(): array
    {
        return $this->itemsTrade;
    }

    public function getSlotNextPage(): int
    {
        return $this->slotNextPage;
    }

    public function getSlotPrevious(): int
    {
        return $this->slotPrevious;
    }

    public function getItemNextPage(): string
    {
        return $this->itemNextPage;
    }

    public function getItemPrevious(): string
    {
        return $this->itemPrevious;
    }

    public function getItemSpace(): Item
    {
        return $this->itemSpace;
    }

    public function setUid(string $uid): void
    {
        $this->uid = $uid;
    }

    public function setTypeTrade(string $typeTrade): void
    {
        $this->typeTrade = $typeTrade;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setItemsBackGround(array $slotsBackGround): void
    {
        $this->itemsBackGround = $slotsBackGround;
    }

    public function setItemsTrade(array $slotsTrade): void
    {
        $this->itemsTrade = $slotsTrade;
    }

    public function setSlotNextPage(int $slotNextPage): void
    {
        $this->slotNextPage = $slotNextPage;
    }

    public function setSlotPrevious(int $slotPrevious): void
    {
        $this->slotPrevious = $slotPrevious;
    }

    public function setItemNextPage(string $itemNextPage): void
    {
        $this->itemNextPage = $itemNextPage;
    }

    public function setItemPrevious(string $itemPrevious): void
    {
        $this->itemPrevious = $itemPrevious;
    }

    public function setItemBackGround(int $slot, string $item): void
    {
        $this->itemsBackGround[$slot] = $item;
    }

    public function setItemSpace(Item $itemSpace): void
    {
        $this->itemSpace = $itemSpace;
    }

    public function removeItemBackGround(int $slot): void
    {
        unset($this->itemsBackGround[$slot]);
    }

    public function addItemTrade(ItemTrade $itemTrade): void
    {
        $this->itemsTrade[] = $itemTrade->toArray();
    }

    public function isItemBackGround(string $item): bool
    {
        return in_array($item, $this->itemsBackGround);
    }

    public function toArray(): array
    {
        return [
            'uid' => $this->uid,
            'typeTrade' => $this->typeTrade,
            'name' => $this->name,
            'description' => $this->description,
            'itemsBackGround' => $this->itemsBackGround,
            'itemsTrade' => $this->itemsTrade,
            'slotNextPage' => $this->slotNextPage,
            'slotPrevious' => $this->slotPrevious,
            'itemNextPage' => $this->itemNextPage,
            'itemPrevious' => $this->itemPrevious,
            'itemSpace' => ItemUtil::encodeItem($this->itemSpace),
        ];
    }

    /**
     * @throws Throwable
     */
    public function getListPageItemsTrade(int $limitItemsInPage, int $page): Promise
    {
        return new Promise(function ($resolve, $reject) use ($limitItemsInPage, $page) {
            try {
                $currentPage = 1;
                $listPageItemsTrade = [];

                foreach ($this->itemsTrade as $itemTrade) {
                    $listPageItemsTrade[$currentPage][] = $itemTrade;
                    if (count($listPageItemsTrade[$currentPage]) === $limitItemsInPage) {
                        if ($currentPage === $page) break;
                        $currentPage++;
                    }
                    FiberManager::wait();
                }

                $resolve($listPageItemsTrade[$page] ?? []);
            } catch (Throwable $e) {
                $reject($e);
            }
        });
    }

    public function getMaxListPageItemsTrade(int $limitItemsInPage): float
    {
        return ceil(count($this->itemsTrade) / $limitItemsInPage);
    }

    public static function fromArray(array $data): self
    {
        $tradeInterfaceStructData = new self($data['name'], $data['description']);
        $tradeInterfaceStructData->setUid($data['uid']);
        $tradeInterfaceStructData->setTypeTrade($data['typeTrade']);
        $tradeInterfaceStructData->setItemsBackGround($data['itemsBackGround']);
        $tradeInterfaceStructData->setItemsTrade($data['itemsTrade']);
        $tradeInterfaceStructData->setSlotNextPage($data['slotNextPage']);
        $tradeInterfaceStructData->setSlotPrevious($data['slotPrevious']);
        $tradeInterfaceStructData->setItemNextPage($data['itemNextPage']);
        $tradeInterfaceStructData->setItemPrevious($data['itemPrevious']);
        $tradeInterfaceStructData->setItemSpace(ItemUtil::decodeItem($data['itemSpace']));
        return $tradeInterfaceStructData;
    }

    /**
     * @throws Throwable
     */
    public function removeItemTrade(
        Item|null $itemA,
        Item|null $itemB,
        Item|null $itemC,
        Item      $itemOutput
    ): Async
    {
        return new Async(function () use ($itemA, $itemB, $itemC, $itemOutput): bool {
            $qualifiedA = $qualifiedB = $qualifiedC = $qualifiedOutput = false;
            foreach ($this->itemsTrade as $case => $itemTrade) {
                $itemTrade = ItemTrade::fromArray($itemTrade);
                if ($itemTrade->getItemOutput()->equals($itemOutput)) {
                    /** @var Item|null $item */
                    foreach ([$itemA, $itemB, $itemC, $itemOutput] as $item) {
                        if ($item === null) continue;
                        if ($itemTrade->getItemA()->equals($item)) $qualifiedA = true;
                        if ($itemTrade->getItemB()->equals($item)) $qualifiedB = true;
                        if ($itemTrade->getItemC()->equals($item)) $qualifiedC = true;
                        if ($itemTrade->getItemOutput()->equals($item)) $qualifiedOutput = true;
                        FiberManager::wait();
                    }
                    if ($qualifiedA && $qualifiedB && $qualifiedC && $qualifiedOutput) {
                        unset($this->itemsTrade[$case]);
                        $this->save();
                        break;
                    }
                }
                FiberManager::wait();
            }

            return $qualifiedA && $qualifiedB && $qualifiedC && $qualifiedOutput;
        });
    }

    public function save(): void
    {
        try {
            $config = VNPCTradeGUI::getConfigManager()::createTradeConfig(ColorUtil::removeColor($this->name));
            $config->setAll($this->toArray());
            $config->save();
        } catch (Throwable $e) {
            VNPCTradeGUI::getInstance()->getLogger()->error($e->getMessage());
        }
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

}
