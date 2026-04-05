<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\command\commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\command\SubCommand;
use venndev\vnpctradegui\data\Permissions;
use venndev\vnpctradegui\manager\InvAddTrade;
use Throwable;

final class Add extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "add",
            "Add new item for one trade menu",
            "add",
            [
                "ad"
            ]
        );

        $this->setPermission(Permissions::COMMAND_ADD);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                $name = $args[0];
                $sender->sendMessage("Opening menu to add VNPCTradeGUI with name: " . $name);
                new InvAddTrade($sender, $name);
            } else {
                $sender->sendMessage("Usage: /vnpctradegui add <name>");
            }
        }
    }

}
