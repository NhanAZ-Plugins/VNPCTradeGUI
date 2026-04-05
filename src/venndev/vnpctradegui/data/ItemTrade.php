<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\data;

use pocketmine\item\Item;
use venndev\vnpctradegui\utils\ItemUtil;

final class ItemTrade
{

    public function __construct(
        private ?Item $itemA = null,
        private ?Item $itemB = null,
        private ?Item $itemC = null,
        private ?Item $itemOutput = null
    )
    {
        //TODO: Implement
    }

    public function getItemA(): ?Item
    {
        return $this->itemA;
    }

    public function getItemB(): ?Item
    {
        return $this->itemB;
    }

    public function getItemC(): ?Item
    {
        return $this->itemC;
    }

    public function getItemOutput(): ?Item
    {
        return $this->itemOutput;
    }

    public function setItemA(?Item $itemA): void
    {
        $this->itemA = $itemA;
    }

    public function setItemB(?Item $itemB): void
    {
        $this->itemB = $itemB;
    }

    public function setItemC(?Item $itemC): void
    {
        $this->itemC = $itemC;
    }

    public function setItemOutput(?Item $itemOutput): void
    {
        $this->itemOutput = $itemOutput;
    }

    public function getItemOffer(): array
    {
        return [$this->itemA, $this->itemB, $this->itemC];
    }

    public function addItemOffer(Item $item): void
    {
        if ($this->itemA === null) {
            $this->itemA = $item;
        } elseif ($this->itemB === null) {
            $this->itemB = $item;
        } elseif ($this->itemC === null) {
            $this->itemC = $item;
        }
    }

    public function toArray(): array
    {
        return [
            "itemA" => $this->itemA !== null ? ItemUtil::encodeItem($this->itemA) : null,
            "itemB" => $this->itemB !== null ? ItemUtil::encodeItem($this->itemB) : null,
            "itemC" => $this->itemC !== null ? ItemUtil::encodeItem($this->itemC) : null,
            "itemOutput" => $this->itemOutput !== null ? ItemUtil::encodeItem($this->itemOutput) : null
        ];
    }

    public static function fromArray(array $data): ItemTrade
    {
        $itemA = $data["itemA"] !== null ? ItemUtil::decodeItem($data["itemA"]) : null;
        $itemB = $data["itemB"] !== null ? ItemUtil::decodeItem($data["itemB"]) : null;
        $itemC = $data["itemC"] !== null ? ItemUtil::decodeItem($data["itemC"]) : null;
        $itemOutput = $data["itemOutput"] !== null ? ItemUtil::decodeItem($data["itemOutput"]) : null;
        $itemTrade = new ItemTrade();
        $itemTrade->setItemA($itemA);
        $itemTrade->setItemB($itemB);
        $itemTrade->setItemC($itemC);
        $itemTrade->setItemOutput($itemOutput);
        return $itemTrade;
    }

    public function getResultEquals(ItemTrade $itemTrade): array
    {
        $itemA = clone $this->itemA;
        $itemB = clone $this->itemB;
        $itemC = clone $this->itemC;

        $itemTradeA = clone $itemTrade->getItemA();
        $itemTradeB = clone $itemTrade->getItemB();
        $itemTradeC = clone $itemTrade->getItemC();

        $checkAndRemoveTagBarrier = function (Item $item): void {
            if ($item->getNamedTag()->getTag("barrier") !== null) {
                $item->getNamedTag()->removeTag("barrier");
            }
        };

        $qualifiedA = $qualifiedB = $qualifiedC = false;
        $listItems = [$itemA, $itemB, $itemC];
        $listItemsTrade = [$itemTradeA, $itemTradeB, $itemTradeC];

        /** @var Item $item */
        foreach ($listItems as $item) {
            $checkAndRemoveTagBarrier($item);
            foreach ($listItemsTrade as $keyTrade => $itemTrade) {
                $checkAndRemoveTagBarrier($itemTrade);
                if ($item->equals($itemTrade) && $item->getCount() >= $itemTrade->getCount()) {
                    if (!$qualifiedA) {
                        $qualifiedA = true;
                        unset($listItemsTrade[$keyTrade]);
                    } elseif (!$qualifiedB) {
                        $qualifiedB = true;
                        unset($listItemsTrade[$keyTrade]);
                    } elseif (!$qualifiedC) {
                        $qualifiedC = true;
                        unset($listItemsTrade[$keyTrade]);
                    }

                    $item->setCount($item->getCount() - $itemTrade->getCount());
                }
            }
        }

        return [
            "result" => $qualifiedA && $qualifiedB && $qualifiedC,
            "itemA" => $itemA,
            "itemB" => $itemB,
            "itemC" => $itemC
        ];
    }

}
