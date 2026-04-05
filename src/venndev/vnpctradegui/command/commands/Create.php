<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\command\commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\command\SubCommand;
use venndev\vnpctradegui\data\Permissions;
use venndev\vnpctradegui\data\TradeStructData;
use venndev\vnpctradegui\utils\TypeTradeMenu;
use Throwable;

final class Create extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "create",
            "Create a new trade menu",
            "create",
            [
                "cr"
            ]
        );

        $this->setPermission(Permissions::COMMAND_CREATE);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                $name = $args[0];
                $type = TypeTradeMenu::CLASSIC;
                if (isset($args[1]) && in_array($args[1], [TypeTradeMenu::CLASSIC, TypeTradeMenu::MODERN])) {
                    $type = $args[1];
                }

                $trade = new TradeStructData($name, "Type your description if you want here!", $type);
                $trade->save();

                $sender->sendMessage("Creating VNPCTradeGUI with name: " . $name . " and type: " . $type);
            } else {
                $sender->sendMessage("Usage: /vnpctradegui create <name> <type>");
            }
        }
    }

}
