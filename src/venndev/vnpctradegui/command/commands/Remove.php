<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\command\commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vnpctradegui\command\SubCommand;
use venndev\vnpctradegui\data\Permissions;
use venndev\vnpctradegui\manager\InvRemoveTrade;
use Throwable;

final class Remove extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "remove",
            "Open menu remove items trade menu",
            "remove",
            [
                "rm"
            ]
        );

        $this->setPermission(Permissions::COMMAND_REMOVE);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                $name = $args[0];
                $page = $args[1] ?? 1;
                new InvRemoveTrade($sender, $name, $page);
            } else {
                $sender->sendMessage("Usage: /vnpctradegui remove <name> <page(number)>");
            }
        }
    }

}
