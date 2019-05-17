<?php

namespace SupplyCrates;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\block\Block;	
use pocketmine\event\Listener;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\HugeExplodeSeedParticle;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\particle\Particle;
use pocketmine\level\sound\PopSound;
use SupplyCrates\Main;

class particleTask extends Task implements Listener{

    public $plugin;
    public $secs=59;
    public $mins=3;
    public $chest;
    public $ftext;
    
    public $x;
    public $y;
    public $z;
    public $level;
    public $player;
	
	public function __construct(Main $plugin,int $x,int $y,int $z,Player $player){
	$this->plugin = $plugin;
	$this->x = (int)$x+0.50;
	$this->y = (int)$y;
	$this->z = (int)$z+0.50;
	$this->player = $player;
	$this->level = $player->getLevel();
	$this->chest = $this->y+29;
	$this->ftext = new FloatingTextParticle(new Vector3($this->x,$this->y+0.50,$this->z),C::YELLOW.C::UNDERLINE."INCOMING CRATE...","");
	}
	
	public function getPlugin() {
          return $this->plugin;
      }
	  	 
      public function onRun(int $tick) {
	 //Column of particles
	 $upY = $this->y;
	 for($cnt=30+$this->y;$cnt>$upY;$upY++){
	 $this->level->addParticle(new DustParticle(new Vector3($this->x,$upY,$this->z),255,255,255));
	 }
	 
	 //Floating Text
	if($this->chest == $this->y+29){
	$this->level->addParticle($this->ftext);  
	}
	 
	//place chest
	 $bl1 = Block::get(Block::CHEST);
	$pos = new Vector3($this->x,$this->chest,$this->z);
	$this->level->setBlock($pos, $bl1, false, false);
	//removes chest
	 $bl1 = Block::get(Block::AIR);
	 $pos = new Vector3($this->x,$this->chest+1,$this->z);
	 $this->level->setBlock($pos, $bl1, false, false);
	 
	 if($this->chest > $this->y){
	 $this->chest--;
	 }else{
	 $this->ftext->setInvisible(true);
	 $this->level->addParticle($this->ftext);
	 $this->level->addParticle(new HugeExplodeSeedParticle(new Vector3($this->x,$this->y,$this->z)));
	 $this->level->addSound(new PopSound(new Vector3($this->x,$this->y,$this->z)));
	 $this->stop();  
	 }
			  //$this->getPlugin()->removeTask($this->getTaskId()); Stops the task
          }

		public function stop(){
		$this->getPlugin()->stopTask($this->getTaskId());
	  }



      }	  