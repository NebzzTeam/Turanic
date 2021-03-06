<?php

/*
 *
 *    _______                    _
 *   |__   __|                  (_)
 *      | |_   _ _ __ __ _ _ __  _  ___
 *      | | | | | '__/ _` | '_ \| |/ __|
 *      | | |_| | | | (_| | | | | | (__
 *      |_|\__,_|_|  \__,_|_| |_|_|\___|
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Turanic
 *
 */

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;

class Wheat extends Crops {

	protected $id = self::WHEAT_BLOCK;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Wheat Block";
	}

    public function getDropsForCompatibleTool(Item $item) : array{
        if($this->meta >= 0x07){
            return [
                Item::get(Item::WHEAT),
                Item::get(Item::WHEAT_SEEDS, 0, mt_rand(0, 3))
            ];
        }else{
            return [
                Item::get(Item::WHEAT_SEEDS)
            ];
        }
    }

    public function getPickedItem(): Item{
        return Item::get(Item::WHEAT_SEEDS);
    }

    public function ticksRandomly(): bool{
        return true;
    }
}
