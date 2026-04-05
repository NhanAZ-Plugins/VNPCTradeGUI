<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use venndev\vnpctradegui\command\commands\Add;
use venndev\vnpctradegui\command\commands\Create;
use venndev\vnpctradegui\command\commands\About;
use venndev\vnpctradegui\command\commands\CreateNPC;
use venndev\vnpctradegui\command\commands\Delete;
use venndev\vnpctradegui\command\commands\DeleteNPC;
use venndev\vnpctradegui\command\commands\Open;
use venndev\vnpctradegui\command\commands\Remove;
use venndev\vnpctradegui\command\commands\Setup;
use venndev\vnpctradegui\data\Permissions;
use venndev\vnpctradegui\VNPCTradeGUI;

final class VNPCTradeGUICommand extends Command implements PluginOwned
{

    public function __construct(
        private readonly VNPCTradeGUI $plugin
    )
    {
        parent::__construct(
            "vnpctradegui",
            "VNPCTradeGUI command",
            "/vnpctradegui",
            [
                "vntg"
            ]
        );

        $this->setPermission(Permissions::COMMAND);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return false;
        }

        $subCommands = [
            new About(),
            new Create(),
            new Delete(),
            new Remove(),
            new Add(),
            new Open(),
            new Setup(),
            new CreateNPC(),
            new DeleteNPC()
        ];

        if (count($args) === 0) {
            $sender->sendMessage("\n\n" . TextFormat::AQUA . "VNPCTradeGUI (" . $this->plugin->getDescription()->getVersion() . ") Commands:");
            foreach ($subCommands as $subCommand) {
                $sender->sendMessage("- " . TextFormat::GREEN . $subCommand->getUsage() . TextFormat::WHITE . " - " . TextFormat::GRAY . $subCommand->getDescription());
            }
        } else {
            foreach ($subCommands as $subCommand) {
                if ($subCommand->checkCommand($sender, $args)) return true;
            }
        }

        return false;
    }

    public function getOwningPlugin(): Plugin
    {
        return $this->plugin;
    }

}
