<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\player;

use pocketmine\player\Player;
use venndev\vnpctradegui\utils\TypeTradeMenu;

final class VPlayer
{

    private string $typeTrade = TypeTradeMenu::CLASSIC;

    private bool $isOpenTradeMenu = false;

    private bool $isModeSetupNPC = false;

    public function __construct(Player $player)
    {
        //TODO: Implement
    }

    public function getTypeTrade(): string
    {
        return $this->typeTrade;
    }

    public function setTypeTrade(string $typeTrade): void
    {
        $this->typeTrade = $typeTrade;
    }

    public function isOpenTradeMenu(): bool
    {
        return $this->isOpenTradeMenu;
    }

    public function setOpenTradeMenu(bool $isOpenTradeMenu): void
    {
        $this->isOpenTradeMenu = $isOpenTradeMenu;
    }

    public function isModeSetupNPC(): bool
    {
        return $this->isModeSetupNPC;
    }

    public function setModeSetupNPC(bool $isModeSetupNPC): void
    {
        $this->isModeSetupNPC = $isModeSetupNPC;
    }

    public function openTradeMenu(string $typeMenu): void
    {
        $this->isOpenTradeMenu = true;
        $this->typeTrade = $typeMenu;
    }

    public function closeTradeMenu(): void
    {
        $this->isOpenTradeMenu = false;
        $this->typeTrade = TypeTradeMenu::CLASSIC;
    }

}
