<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\command\commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vnpctradegui\command\SubCommand;
use venndev\vnpctradegui\data\Permissions;
use venndev\vnpctradegui\entity\VillagerNPC;
use venndev\vnpctradegui\manager\InvAddTrade;
use Throwable;

final class CreateNPC extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "createnpc",
            "Create one npc with trade",
            "createnpc",
            [
                "cnpc"
            ]
        );

        $this->setPermission(Permissions::COMMAND_CREATE_NPC);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            if (isset($args[0]) && isset($args[1])) {
                $nameTrade = $args[0];
                $name = implode(" ", array_slice($args, 1));

                try {
                    $npc = new VillagerNPC($sender->getLocation());
                    $npc->setName($name);
                    $npc->setTradeName($nameTrade);
                    $npc->spawnToAll();

                    $sender->sendMessage(TextFormat::GREEN . "VillagerNPC created with name: " . $name . " and trade name: " . $nameTrade);
                } catch (Throwable $e) {
                    $sender->sendMessage(TextFormat::RED . "Error creating VillagerNPC: " . $e->getMessage());
                }
            } else {
                $sender->sendMessage("Usage: /vnpctradegui createnpc <name-trade> <name>");
            }
        }
    }

}
