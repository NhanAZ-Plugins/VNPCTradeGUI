<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\player;

use pocketmine\inventory\Inventory;

final class InventoryMenuPlayerHandler
{

    private ?Inventory $inventory = null;

    public function __construct()
    {
        //TODO: Implement
    }

    public function getInventory(): ?Inventory
    {
        return $this->inventory;
    }

    public function setInventory(Inventory $inventory): void
    {
        $this->inventory = $inventory;
    }

}
