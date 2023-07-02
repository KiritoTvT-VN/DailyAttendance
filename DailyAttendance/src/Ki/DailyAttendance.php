<?php

namespace Ki;

use pocketmine\player\Player;
use pocketmine\command\{Command, CommandSender};
use pocketmine\plugin\PluginBase as PB;
use pocketmine\event\Listener as L;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use jojoe77777\FormAPI\{SimpleForm, CustomForm};
use pocketmine\economyapi\EconomyAPI;
use pocketmine\world\Position;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class DailyAttendance extends PB implements L {

	public function onEnable() : void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->check = new Config($this->getDataFolder() . "checks.yml", Config::YAML);
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $this->getScheduler()->scheduleRepeatingTask(new CheckingTask($this), 20);
    }

     public function onDisable() : void {
        $this->check->save();
    }

    public function onJoin(PlayerJoinEvent $ev){
        $player = $ev->getPlayer();
        if(!$this->check->exists($player->getName())){
            $this->check->set($player->getName(), 0);
            $this->check->save();
        }
    }

    public function reset(){
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $time = date("h:i:s");
        if($time == "24:00:00" or $time == "23:59:59"){
            foreach($this->check->getAll() as $all => $data){
                $this->check->set($all, 0);
                $this->check->save();
            }
        }
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
    	switch($cmd->getName()){
    		case "attendance":
    		    if(!$sender instanceof Player){
    		    	$sender->sendMessage("§l§c•§e Please Use In Game");
    		    	return true;
    		    }else{
    		    	$this->OpenMenu($sender);
    		    }
    		break;
    	}
    	return true;
    }

    public function OpenMenu(Player $sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = new SimpleForm(function (Player $sender, ?int $data = null){
		$result = $data;
		if($result === null){
			return;
		    }
			switch($result){
				case 0:
				$this->reward($sender);
				break;
			}
		}); 
		$form->setTitle("§l§6Daily Attendance");
		$form->addButton("§aReceive");
		$form->sendToPlayer($sender);
			return $form;
	}

	public function reward($sender){
		if((int)$this->check->get($sender->getName()) < 1){
			$this->check->set($sender->getName(), (int)$this->check->get($sender->getName()) + 1);
            $this->check->save();
            $reward = mt_rand(1000, 5000);
            EconomyAPI::getInstance()->addMoney($sender, $reward);
            $sender->sendMessage("§l§c• §eYou Received " . $reward ." Money From the Attendance Gift");
            $position = $sender->getPosition();
        	$packet = new PlaySoundPacket();
        	$packet->soundName = "random.levelup";
        	$packet->x = $position->getX();
        	$packet->y = $position->getY();
        	$packet->z = $position->getZ();
        	$packet->volume = 1;
        	$packet->pitch = 1;
        	$sender->getNetworkSession()->sendDataPacket($packet);
		}
		if((int)$this->check->get($sender->getName()) > 1){
            $sender->sendMessage("§l§c• §eYou Received Attendance Gift Today");
            $position = $sender->getPosition();
        	$packet = new PlaySoundPacket();
        	$packet->soundName = "mob.horse.angry";
        	$packet->x = $position->getX();
        	$packet->y = $position->getY();
        	$packet->z = $position->getZ();
        	$packet->volume = 1;
        	$packet->pitch = 1;
        	$sender->getNetworkSession()->sendDataPacket($packet);
		}
	}

}