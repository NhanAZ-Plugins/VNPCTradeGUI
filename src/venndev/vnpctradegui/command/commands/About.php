<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\command\commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\command\SubCommand;
use venndev\vnpctradegui\data\Permissions;
use venndev\vnpctradegui\VNPCTradeGUI;

final class About extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "about",
            "About VNPCTradeGUI",
            "about",
            [
                "abt"
            ]
        );

        $this->setPermission(Permissions::COMMAND_ABOUT);
    }

    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            $sender->sendMessage("\n\nVNPCTradeGUI v" . VNPCTradeGUI::getInstance()->getDescription()->getVersion());
            $sender->sendMessage("Developed by VennDev");
            $sender->sendMessage("Github: https://github.com/VennDev");
            $sender->sendMessage("Email: pnam5005@gmail.com");
        }
    }

}
