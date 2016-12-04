<?php
namespace pocketmine\level\sound;

use pocketmine\math\Vector3;
use pocketmine\network\protocol\LevelEventPacket;

class ClickSound extends GenericSound{
	public function __construct(Vector3 $pos, $pitch = 0){
		parent::__construct($pos, 'random.click', $pitch);
	}
}
