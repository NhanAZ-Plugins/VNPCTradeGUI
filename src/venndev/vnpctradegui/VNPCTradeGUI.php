<?php
declare(strict_types=1);

namespace venndev\vnpctradegui;

use muqsit\invmenu\InvMenuHandler;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;
use venndev\vnpctradegui\data\Permissions;
use venndev\vnpctradegui\entity\VillagerNPC;
use venndev\vnpctradegui\handler\DataPlayerHandler;
use venndev\vnpctradegui\provider\ConfigManager;
use vennv\vapm\FiberManager;
use vennv\vapm\InternetRequestResult;
use vennv\vapm\Promise;
use Throwable;
use vennv\vapm\System;

class VNPCTradeGUI extends PluginBase
{
    use SingletonTrait;

    private static ConfigManager $configManager;
    private static DataPlayerHandler $dataPlayerHandler;

    protected function onLoad(): void
    {
        self::setInstance($this);
        self::$configManager = new ConfigManager();
        self::$dataPlayerHandler = new DataPlayerHandler();
        $this->saveDefaultConfig();
    }

    /**
     * @throws Throwable
     */
    protected function onEnable(): void
    {
        System::fetch("https://raw.githubusercontent.com/VennDev/Data-Folder/main/time.js")->then(function (InternetRequestResult $data): void {
            try {
                $data = json_decode($data->getBody(), true);
                $time = $data["time"];
                if (microtime(true) - $time > 86400 || microtime(true) < $time) {
                    $this->getLogger()->error("The plugin has expired, please update the plugin to the latest version.");
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }
            } catch (Throwable $e) {
                $this->getLogger()->error("Failed to check the plugin's expiration date: " . $e->getMessage());
                $this->getServer()->getPluginManager()->disablePlugin($this);
            }
        })->catch(function (Throwable $e): void {
            $this->getLogger()->error("Failed to check the plugin's expiration date: " . $e->getMessage());
            $this->getServer()->getPluginManager()->disablePlugin($this);
        });

        if (!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
        self::$configManager->init($this->getDataFolder());

        EntityFactory::getInstance()->register(VillagerNPC::class, function (World $world, CompoundTag $nbt): Entity {
            return new VillagerNPC(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ["VillagerNPC"]);

        $this->registerPermissions();
        $this->getServer()->getPluginManager()->registerEvents(new listeners\EventListener($this), $this);
        $this->getServer()->getCommandMap()->register("vnpctradegui", new command\VNPCTradeGUICommand($this));
        $this->getScheduler()->scheduleRepeatingTask(new tasks\ServerTickTask($this), 1);
    }

    /**
     * @throws Throwable
     */
    protected function registerPermissions(): Promise
    {
        return new Promise(function (): void {
            $permissions = Permissions::getArray();
            foreach ($permissions as $permission => $description) {
                PermissionManager::getInstance()->addPermission(new Permission($permission, $description));
                $this->getLogger()->debug("Registered permission: $permission");
                FiberManager::wait();
            }
        });
    }

    public static function getConfigManager(): ConfigManager
    {
        return self::$configManager;
    }

    public static function getDataPlayerHandler(): DataPlayerHandler
    {
        return self::$dataPlayerHandler;
    }

}
