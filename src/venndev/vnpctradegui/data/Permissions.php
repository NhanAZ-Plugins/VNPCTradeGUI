<?php
declare(strict_types=1);

namespace venndev\vnpctradegui\data;

final class Permissions
{

    public const COMMAND = "vnpctradegui.command";
    public const COMMAND_ABOUT = "vnpctradegui.command.about";
    public const COMMAND_CREATE = "vnpctradegui.command.create";
    public const COMMAND_DELETE = "vnpctradegui.command.delete";
    public const COMMAND_REMOVE = "vnpctradegui.command.remove";
    public const COMMAND_SETUP = "vnpctradegui.command.setup";
    public const COMMAND_ADD = "vnpctradegui.command.add";
    public const COMMAND_OPEN = "vnpctradegui.command.open";
    public const COMMAND_CREATE_NPC = "vnpctradegui.command.create.npc";
    public const COMMAND_DELETE_NPC = "vnpctradegui.command.delete.npc";

    public static function getArray(): array
    {
        return [
            self::COMMAND => "vnpctradegui command",
            self::COMMAND_ABOUT => "vnpctradegui command about",
            self::COMMAND_CREATE => "vnpctradegui command create",
            self::COMMAND_DELETE => "vnpctradegui command delete",
            self::COMMAND_REMOVE => "vnpctradegui command remove",
            self::COMMAND_SETUP => "vnpctradegui command setup",
            self::COMMAND_ADD => "vnpctradegui command add",
            self::COMMAND_OPEN => "vnpctradegui command open",
            self::COMMAND_CREATE_NPC => "vnpctradegui command create npc",
            self::COMMAND_DELETE_NPC => "vnpctradegui command delete npc"
        ];
    }

}
