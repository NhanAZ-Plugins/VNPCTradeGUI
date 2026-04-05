<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\command\commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vnpctradegui\command\SubCommand;
use venndev\vnpctradegui\data\Permissions;
use venndev\vnpctradegui\VNPCTradeGUI;
use Throwable;

final class Delete extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "delete",
            "Delete a trade menu",
            "delete",
            [
                "dl"
            ]
        );

        $this->setPermission(Permissions::COMMAND_DELETE);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                $name = $args[0];
                $config = VNPCTradeGUI::getConfigManager()::getTradeConfig($name);
                if ($config !== null) {
                    VNPCTradeGUI::getConfigManager()::deleteTradeConfig($name);
                    $sender->sendMessage("VNPCTradeGUI with name: " . $name . " deleted!");
                } else {
                    $sender->sendMessage("VNPCTradeGUI with name: " . $name . " not found!");
                }
            } else {
                $sender->sendMessage("Usage: /vnpctradegui delete <name>");
            }
        }
    }

}
