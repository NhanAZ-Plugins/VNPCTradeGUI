<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\command\commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vmskyblock\command\SubCommand;
use venndev\vnpctradegui\data\Permissions;
use venndev\vnpctradegui\VNPCTradeGUI;

final class DeleteNPC extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "deletenpc",
            "Delete one npc with trade",
            "deletenpc",
            [
                "dnpc"
            ]
        );

        $this->setPermission(Permissions::COMMAND_DELETE_NPC);
    }

    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            $data = VNPCTradeGUI::getDataPlayerHandler()::getData($sender);
            if ($data !== null) {
                $data->setModeSetupNPC(true);
                $sender->sendMessage(TextFormat::AQUA . "Right-click the NPC to delete it.");
            }
        }
    }

}
