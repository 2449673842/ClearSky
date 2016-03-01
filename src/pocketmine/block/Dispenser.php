<?php

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\Player;
use pocketmine\tile\Dispenser as DispenserTile;
use pocketmine\tile\Tile;
use pocketmine\entity\ProjectileSource;
use pocketmine\math\Vector3;
use pocketmine\math\Vector2;
use pocketmine\tile\Chest;

class Dispenser extends Solid implements ProjectileSource{
	protected $id = self::DISPENSER;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName(){
		return "Dispenser";
	}

	public function canBeActivated(){
		return true;
	}

	public function getHardness(){
		return 3.5;
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($player->pitch <= 45 && $player->pitch = -45){
			$faces = [
					0 => 4,
					1 => 2,
					2 => 5,
					3 => 3,
			];
			$this->meta = $faces[$player->getDirection()];
		}
		elseif($player->pitch > 45){
			$this->meta = 1;
		}
		elseif($player->pitch <= -45){
			$this->meta = 0;
		}
		$this->getLevel()->setBlock($block, $this, true, true);
		$nbt = new Compound("", [
				new Enum("Items", []),
				new String("id", Tile::DISPENSER),
				new Int("x", $this->x),
				new Int("y", $this->y),
				new Int("z", $this->z)
		]);
		$nbt->Items->setTagType(NBT::TAG_Compound);
		
		if($item->hasCustomName()){
			$nbt->CustomName = new String("CustomName", $item->getCustomName());
		}
		
		if($item->hasCustomBlockData()){
			foreach($item->getCustomBlockData() as $key => $v){
				$nbt->{$key} = $v;
			}
		}
		
		$tile = Tile::createTile(Tile::DISPENSER, $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
		
		return true;
	}

	public function getDrops(Item $item){
		$drops = [];
		if($item->isPickaxe() >= 1){
			$drops[] = [Item::DISPENSER,0,1];
		}
		
		return $drops;
	}
}