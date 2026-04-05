<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\command\commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\command\SubCommand;
use venndev\vnpctradegui\data\Permissions;
use venndev\vnpctradegui\manager\InvSetupTradeInterface;
use Throwable;

final class Setup extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "setup",
            "Setup VNPCTradeGUI",
            "setup",
            [
                "sp"
            ]
        );

        $this->setPermission(Permissions::COMMAND_SETUP);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                $name = $args[0];
                $sender->sendMessage("Setting up VNPCTradeGUI with name: " . $name);
                new InvSetupTradeInterface($sender, $name);
            } else {
                $sender->sendMessage("Usage: /vnpctradegui setup <name>");
            }
        }
    }

}
