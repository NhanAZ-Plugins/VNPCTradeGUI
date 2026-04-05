<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\entity;

use pocketmine\entity\Location;
use pocketmine\entity\Villager;
use pocketmine\nbt\tag\CompoundTag;

final class VillagerNPC extends Villager
{

    private string $tradeName = "";
    private string $name = "VillagerNPC";

    public function __construct(
        Location     $location,
        ?CompoundTag $nbt = null,
    )
    {
        parent::__construct($location, $nbt);
    }

    public function getTradeName(): string
    {
        return $this->tradeName;
    }

    public function setTradeName(string $tradeName): void
    {
        $this->tradeName = $tradeName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->setNameTag($name);
    }

    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();
        $nbt->setString("trade_name", $this->tradeName);
        $nbt->setString("name", $this->name);
        return $nbt;
    }

    public function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        if ($nbt->getTag("trade_name") === null) {
            $nbt->setString("trade_name", "");
        }
        if ($nbt->getTag("name") === null) {
            $nbt->setString("name", $this->name);
        }
        $this->tradeName = $nbt->getString("trade_name");
        $this->name = $nbt->getString("name");
        $this->setNameTag($this->name);
        $this->setNameTagVisible();
    }

}
