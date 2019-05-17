<?php
namespace SupplyCrates;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\scheduler\TaskHandler;
use pocketmine\event\Listener;
use pocketmine\level\particle\Particle;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\level\Position;
use pocketmine\level\particle\DustParticle;
use pocketmine\nbt\NBT;
use pocketmine\tile\Tile;
use pocketmine\tile\Chest;
use pocketmine\utils\Config;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\utils\TextFormat as C;
use SupplyCrates\particleTask;

class Main extends PluginBase implements Listener{
    
    public $tasks;
    
    public $x;
    public $y;
    public $z;
    
    public $cd;
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
        
        if(!is_dir($this->getDataFolder())){
			@mkdir($this->getDataFolder());
		}
		if(!file_exists($this->getDataFolder() . "config.yml")){
			$this->saveDefaultConfig();
		}
    }
    
	public function onCommand(CommandSender $sender, Command $cmd, string $label,array $args) : bool {
	if(($cmd->getName()) == "crate"){
	if($this->cd == 0){
	$player = $sender->getPlayer();
	$level = $player->getLevel();
	$this->level = $player->getLevel();
	$this->x = $player->getPosition()->getX();
	$this->y = $player->getPosition()->getY();
	$this->z = $player->getPosition()->getZ();
	
	$x = $player->getPosition()->getX();
	$y = $player->getPosition()->getY();
	$z = $player->getPosition()->getZ();
	
	$task = new particleTask($this,$x,$y,$z,$player);	
    $h = $this->getScheduler()->scheduleRepeatingTask($task,5);
	$task->setHandler($h);
    $this->tasks[$task->getTaskId()] = $task->getTaskId();
    $this->cd=1;
	}else{
	$sender->sendMessage(C::RED.C::UNDERLINE."Cannot send multiple crates at the same time");  
	}
	}
return true;	
	}
	
	public function chestTile(){
	$item = $this->getConfig()->get("Items");
	$nbt = Chest::createNBT(new Vector3($this->x,$this->y,$this->z));
    $tile = Tile::createTile(Tile::CHEST, $this->level, $nbt);
    if($tile instanceOf Chest){
    if(!$item == null){
        
    foreach($item as $i){
    $get = explode(",",$i);
    $items = Item::get((int)$get[0],(int)$get[1],(int)$get[2]);
    //Enchant
    if(isset($get[3])){
    $ench = explode("-",$get[3]);
    if(isset($ench[1])){
    if(is_numeric($ench[0]) and is_numeric($ench[1])){
    $enchantment = Enchantment::getEnchantment($ench[0]);
$enchInstance = new EnchantmentInstance($enchantment, $ench[1]);
$items->addEnchantment($enchInstance);
    }
    }
    }
    //Custom Name
    if(isset($get[4]) and (!$get[4] == "")){
    $items->setCustomName($get[4]);   
    }
    $tile->getInventory()->addItem($items);
    $this->cd=0;
    }
    
    }
	}
	}
	
	public function stopTask($id){
	unset($this->tasks[$id]);
	$this->getScheduler()->cancelTask($id);
	$this->chestTile();
	}
    
    public function onDisable(){
     $this->getLogger()->info("§cOffline");
    }
}
