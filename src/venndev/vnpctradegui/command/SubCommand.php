<?php
namespace venndev\vnpctradegui\command;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

abstract class SubCommand
{

    private bool|string $permission;

    public function __construct(
        private readonly string $name,
        private readonly string $description,
        private readonly string $usage,
        private readonly array  $aliases
    )
    {
        $this->permission = true;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUsage(): string
    {
        return $this->usage;
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function getPermission(): bool|string
    {
        return $this->permission;
    }

    public function setPermission(bool|string $permission): void
    {
        $this->permission = $permission;
    }

    public function checkCommand(CommandSender $sender, array $args): bool
    {
        if (isset($args[0]) && ($args[0] === $this->name || in_array($args[0], $this->aliases))) {
            if ($sender instanceof Player && $this->permission !== true && !$sender->hasPermission($this->permission)) {
                $sender->sendMessage(TextFormat::RED . "You do not have permission to use this command.");
                return false;
            }

            unset($args[0]);
            $args = array_values($args);
            $this->execute($sender, $args);

            return true;
        }

        return false;
    }

    protected function execute(CommandSender $sender, array $args): void
    {
        // TODO: Implement execute() method.
    }

}
