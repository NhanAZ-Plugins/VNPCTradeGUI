<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\command\commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\command\SubCommand;
use venndev\vnpctradegui\data\Permissions;
use venndev\vnpctradegui\manager\InvTrade;
use Throwable;

final class Open extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "open",
            "Open one trade menu",
            "open",
            [
                "opn"
            ]
        );

        $this->setPermission(Permissions::COMMAND_OPEN);
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
                new InvTrade($sender, $name, $page);
            } else {
                $sender->sendMessage("Usage: /vnpctradegui open <name> <page(number)>");
            }
        }
    }

}
