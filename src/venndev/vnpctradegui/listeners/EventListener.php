<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\listeners;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Throwable;
use venndev\vnpctradegui\entity\VillagerNPC;
use venndev\vnpctradegui\manager\InvTrade;
use venndev\vnpctradegui\player\VPlayer;
use venndev\vnpctradegui\VNPCTradeGUI;

final readonly class EventListener implements Listener
{

    public function __construct(
        private VNPCTradeGUI $plugin
    )
    {
        //TODO: Implement constructor
    }

    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $this->getPlugin()->getDataPlayerHandler()::setData($player, new VPlayer($player));
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $this->getPlugin()->getDataPlayerHandler()::removeData($player);
    }

    /**
     * @throws Throwable
     */
    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void
    {
        $entity = $event->getEntity();
        $attacker = $event->getDamager();
        if ($entity instanceof VillagerNPC) {
            if ($attacker instanceof Player) {
                $vPlayer = $this->getPlugin()->getDataPlayerHandler()::getData($attacker);
                if ($vPlayer !== null && $vPlayer->isModeSetupNPC()) {
                    $entity->close();
                    $vPlayer->setModeSetupNPC(false);
                    $attacker->sendMessage(TextFormat::RED . "You had removed the NPC.");
                } else {
                    new InvTrade($attacker, $entity->getTradeName());
                }
            }
            $event->cancel();
        }
    }

    public function getPlugin(): VNPCTradeGUI
    {
        return $this->plugin;
    }

}
