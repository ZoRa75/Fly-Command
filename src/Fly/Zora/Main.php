<?php

namespace Fly\Zora;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use Fly\Zora\Commands\Fly;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {

    private $flightDataPath;
    private $flightData;
    public static Main $instance;

    public function onEnable(): void {
        self::$instance = $this;   
        $this->flightDataPath = $this->getDataFolder() . "flight_data.json";
        $this->loadFlightData();

        $this->getServer()->getLogger()->info("Plugin Fly activé");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register("", new Fly($this, $this->flightData));
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->getResource("config.yml");
        $this->saveConfig();;
    }

    public static function getInstance(): Main
    {
        return self::$instance;
    }

    public static function getConfigFile(): Config
    {
        return new Config(self::getInstance()->getDataFolder() . "config.yml", Config::YAML); 
    }
    
    public function initResources(): bool 
    { 
        @mkdir($this->getDataFolder()); 
        @$this->saveResource("config.yml"); 
        if (!file_exists($this->getDataFolder() . "config.yml")) 
        {
            $this->getLogger()->error("Impossible de charger la configuration !"); 
            return false;
        } 
        return true; 
    }
    
    public function onLoad(): void
    {
        $this->saveConfig();
    }

    public function onDisable(): void {
        $this->saveFlightData();
        $this->getServer()->getLogger()->info("Plugin Fly désactivé");
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $playerName = $player->getName();

        if (isset($this->flightData[$playerName])) {
            $flightStatus = $this->flightData[$playerName];
            $player->setAllowFlight($flightStatus);
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $playerName = $player->getName();
        $flightStatus = $player->getAllowFlight();

        $this->flightData[$playerName] = $flightStatus;
        $this->saveFlightData();
    }

    private function loadFlightData(): void {
        if (file_exists($this->flightDataPath)) {
            $data = file_get_contents($this->flightDataPath);
            $this->flightData = json_decode($data, true) ?? [];
        } else {
            $this->flightData = [];
        }
    }

    private function saveFlightData(): void {
        file_put_contents($this->flightDataPath, json_encode($this->flightData, JSON_PRETTY_PRINT));
    }
}
