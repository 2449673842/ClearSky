<?php

namespace pocketmine\tile;

use pocketmine\inventory\EnchantInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentEntry;
use pocketmine\item\enchantment\EnchantmentList;
use pocketmine\item\Item;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\EnumTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\protocol\CraftingDataPacket;

class EnchantTable extends Spawnable implements InventoryHolder, Container, Nameable{
	/** @var EnchantInventory */
	protected $inventory;
	/** @var Item */
	protected $item;

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		parent::__construct($chunk, $nbt);
		$this->inventory = new EnchantInventory($this);
		$this->namedtag->Items = new EnumTag("Items", []);
		$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		$this->scheduleUpdate();
	}

	public function getName(){
		return isset($this->namedtag->CustomName)?$this->namedtag->CustomName->getValue():"Enchanting Table";
	}

	public function hasName(){
		return isset($this->namedtag->CustomName);
	}

	public function setName($str){
		if($str === ""){
			unset($this->namedtag->CustomName);
			return;
		}
		$this->namedtag->CustomName = new StringTag("CustomName", $str);
	}

	public function close(){
		if($this->closed === false){
			foreach($this->getInventory()->getViewers() as $player){
				$player->removeWindow($this->getInventory());
			}
			parent::close();
		}
	}

	public function saveNBT(){
		parent::saveNBT();
		unset($this->namedtag->Items);
	}

	/**
	 *
	 * @return int
	 */
	public function getSize(){
		return 2;
	}

	/**
	 *
	 * @return EnchantInventory
	 */
	public function getInventory(){
		return $this->inventory;
	}

	/**
	 *
	 * @param
	 *        	$index
	 *        	
	 * @return int
	 */
	protected function getSlotIndex($index){
		foreach($this->namedtag->Items as $i => $slot){
			if($slot["Slot"] === $index){
				return $i;
			}
		}
		return -1;
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int $index        	
	 *
	 * @return Item
	 */
	public function getItem($index){
		$i = $this->getSlotIndex($index);
		if($i < 0){
			return Item::get(Item::AIR, 0, 0);
		}
		else{
			return NBT::getItemHelper($this->namedtag->Items[$i]);
		}
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int $index        	
	 * @param Item $item        	
	 *
	 * @return bool
	 */
	public function setItem($index, Item $item){
		$i = $this->getSlotIndex($index);
		$d = NBT::putItemHelper($item, $index);
		if($item->getId() === Item::AIR or $item->getCount() <= 0){
			if($i >= 0){
				unset($this->namedtag->Items[$i]);
			}
		}
		elseif($i < 0){
			for($i = 0; $i <= $this->getSize(); ++$i){
				if(!isset($this->namedtag->Items[$i])){
					break;
				}
			}
			$this->namedtag->Items[$i] = $d;
		}
		else{
			$this->namedtag->Items[$i] = $d;
		}
		return true;
	}

	public function onUpdate(){
		if(($this->getLevel()->getServer()->getTick() % $this->getLevel()->getServer()->getTicksPerSecondAverage()) == 0){ // Update per second
			$item = $this->inventory->getItem(0);
			if(!isset($this->item) or !$item->deepEquals($this->item)){
				$this->item = $item;
				$enchantmentList = new EnchantmentList(3);
				for($i = 0; $i < 3; $i++){
					$enchantmentList->setSlot($i, new EnchantmentEntry([Enchantment::getEnchantment(mt_rand(0, 24))], 1, "Test"));
				}
				$pk = new CraftingDataPacket();
				$pk->entries = [$enchantmentList];
				foreach($this->getInventory()->getViewers() as $player){
					$windowId = $player->getWindowId($this->getInventory());
					if($windowId > 0){
						$player->dataPacket($pk);
					}
				}
			}
		}
		return true;
	}

	public function getSpawnCompound(){
		$c = new CompoundTag("", [new StringTag("id", Tile::ENCHANT_TABLE),new IntTag("x", (int) $this->x),new IntTag("y", (int) $this->y),new IntTag("z", (int) $this->z)]);
		if($this->hasName()){
			$c->CustomName = $this->namedtag->CustomName;
		}
		return $c;
	}
}