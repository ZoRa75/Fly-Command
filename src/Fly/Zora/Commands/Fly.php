<?php

namespace Fly\Zora\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\Player;
use pocketmine\world\sound\ThrowSound;
use pocketmine\plugin\PluginBase;
use pocketmine\world\sound\TotemUseSound;
use Fly\Zora\Main;

class Fly extends Command{
 /** @var PluginBase */
   private $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("fly", "Active ou désactive le mode de vol", "/fly <joueur>", ["flight"]);
        $this->setPermission("fly.command");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandlabel, array $args){
        $config = Main::getConfigFile();
        if(!$this->testPermission($sender)){
            return false;
        }

        if(count($args) === 0){
			throw new InvalidCommandSyntaxException();
		}

        $playername = $args[0];
        $player = $sender->getServer()->getPlayerByPrefix($playername);

        if($player instanceof Player){
            $message = str_replace("&", "§", strval($config->get("fly-message")));
            $message = str_replace("{player}", $player->getName(), $message);
            $message = str_replace("{status}", $player->getAllowFlight() ? $config->get("fly-status-false") : $config->get("fly-status-true"), $message);
            $player->setAllowFlight(!$player->getAllowFlight());
            $sender->sendMessage($message);
            $perm = str_replace("&", "§", strval($config->get("can-you-fly-message")));
            $perm = str_replace("{status}", $player->getAllowFlight() ? $config->get("fly-status-true") : $config->get("fly-status-false"), $perm);
            $player->sendMessage($perm);
            $player->broadcastSound(new TotemUseSound);
            $this->savePlayerFlightStatus($player);
        } else {
            $sender->sendMessage($config->get("player-not-online-message"));
        }

        return true;
        }

   private function savePlayerFlightStatus(Player $player) {
        $playerName = $player->getName();
        $flightStatus = $player->getAllowFlight();

        $filePath = $this->plugin->getDataFolder() . "flight_data.json";
        $data = $this->loadFlightData($filePath);
        $data[$playerName] = $flightStatus;

        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function loadFlightData(string $filePath): array {
        if (file_exists($filePath)) {
            $data = file_get_contents($filePath);
            return json_decode($data, true) ?? [];
        }
        return [];
    }
}