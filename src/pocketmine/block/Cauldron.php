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

use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerGlassBottleEvent;
use pocketmine\item\Armor;
use pocketmine\item\TieredTool;
use pocketmine\item\Item;
use pocketmine\item\Potion;
use pocketmine\level\sound\ExplodeSound;
use pocketmine\level\sound\GraySplashSound;
use pocketmine\level\sound\SpellSound;
use pocketmine\level\sound\SplashSound;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\Cauldron as TileCauldron;
use pocketmine\tile\Tile;
use pocketmine\utils\Color;

// TODO : NEED UPDATE
class Cauldron extends Solid {

	protected $id = self::CAULDRON_BLOCK;
	protected $itemId = Item::CAULDRON;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getHardness() : float{
		return 2;
	}

	public function getName() : string{
		return "Cauldron";
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel(): int{
        return TieredTool::TIER_WOODEN;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
        Tile::createTile(Tile::CAULDRON, $this->getLevel(), TileCauldron::createNBT($this, $face, $item, $player));

		return $this->getLevel()->setBlock($blockReplace, $this, true, true);
	}

	public function update(){//umm... right update method...?
		$this->getLevel()->setBlock($this, BlockFactory::get($this->id, $this->meta + 1), true);
		$this->getLevel()->setBlock($this, $this, true);//Undo the damage value
	}

	public function isEmpty(){
		return $this->meta === 0x00;
	}

	public function isFull(){
		return $this->meta === 0x06;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		$tile = $this->getLevel()->getTile($this);
		if(!($tile instanceof TileCauldron)){
			return false;
		}
		switch($item->getId()){
			case Item::BUCKET:
				if($item->getDamage() === 0){//empty bucket
					if(!$this->isFull() or $tile->isCustomColor() or $tile->hasPotion()){
						break;
					}
					$bucket = clone $item;
					$bucket->setDamage(8);//water bucket
					Server::getInstance()->getPluginManager()->callEvent($ev = new PlayerBucketFillEvent($player, $this, 0, $item, $bucket));
					if(!$ev->isCancelled()){
						if($player->isSurvival()){
							$player->getInventory()->setItemInHand($ev->getItem());
						}
						$this->meta = 0;//empty
						$this->getLevel()->setBlock($this, $this, true);
						$tile->clearCustomColor();
						$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
					}
				}elseif($item->getDamage() === 8){//water bucket
					if($this->isFull() and !$tile->isCustomColor() and !$tile->hasPotion()){
						break;
					}
					$bucket = clone $item;
					$bucket->setDamage(0);//empty bucket
					Server::getInstance()->getPluginManager()->callEvent($ev = new PlayerBucketEmptyEvent($player, $this, 0, $item, $bucket));
					if(!$ev->isCancelled()){
						if($player->isSurvival()){
							$player->getInventory()->setItemInHand($ev->getItem());
						}
						if($tile->hasPotion()){//if has potion
							$this->meta = 0;//empty
							$tile->setPotionId(-1);//reset potion
							$tile->setSplashPotion(false);
							$tile->clearCustomColor();
							$this->getLevel()->setBlock($this, $this, true);
							$this->getLevel()->addSound(new ExplodeSound($this->add(0.5, 0, 0.5)));
						}else{
							$this->meta = 6;//fill
							$tile->clearCustomColor();
							$this->getLevel()->setBlock($this, $this, true);
							$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
						}
						$this->update();
					}
				}
				break;
			case Item::DYE:
				if($tile->hasPotion()) break;
				$color = Color::getDyeColor($item->getDamage());
				if($tile->isCustomColor()){
					$color = Color::averageColor($color, $tile->getCustomColor());
				}
				if($player->isSurvival()){
					$item->setCount($item->getCount() - 1);
				}
				$tile->setCustomColor($color);
				$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
				$this->update();
				break;
			case Item::LEATHER_CAP:
			case Item::LEATHER_TUNIC:
			case Item::LEATHER_PANTS:
			case Item::LEATHER_BOOTS:
				if($this->isEmpty()) break;
				if($tile->isCustomColor()){
					--$this->meta;
					$this->getLevel()->setBlock($this, $this, true);
					$newItem = clone $item;
					/** @var Armor $newItem */
					$newItem->setCustomColor($tile->getCustomColor());
					$player->getInventory()->setItemInHand($newItem);
					$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
					if($this->isEmpty()){
						$tile->clearCustomColor();
					}
				}else{
					--$this->meta;
					$this->getLevel()->setBlock($this, $this, true);
					$newItem = clone $item;
					/** @var Armor $newItem */
					$newItem->clearCustomColor();
					$player->getInventory()->setItemInHand($newItem);
					$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
				}
				break;
			case Item::POTION:
			case Item::SPLASH_POTION:
				if(!$this->isEmpty() and (($tile->getPotionId() !== $item->getDamage() and $item->getDamage() !== Potion::WATER_BOTTLE) or
						($item->getId() === Item::POTION and $tile->getSplashPotion()) or
						($item->getId() === Item::SPLASH_POTION and !$tile->getSplashPotion()) and $item->getDamage() !== 0 or
						($item->getDamage() === Potion::WATER_BOTTLE and $tile->hasPotion()))
				){//long...
					$this->meta = 0x00;
					$this->getLevel()->setBlock($this, $this, true);
					$tile->setPotionId(-1);//reset
					$tile->setSplashPotion(false);
					$tile->clearCustomColor();
					if($player->isSurvival()){
						$player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
					}
					$this->getLevel()->addSound(new ExplodeSound($this->add(0.5, 0, 0.5)));
				}elseif($item->getDamage() === Potion::WATER_BOTTLE){//水瓶 喷溅型水瓶
					$this->meta += 2;
					if($this->meta > 0x06) $this->meta = 0x06;
					$this->getLevel()->setBlock($this, $this, true);
					if($player->isSurvival()){
						$player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
					}
					$tile->setPotionId(-1);
					$tile->setSplashPotion(false);
					$tile->clearCustomColor();
					$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
				}elseif(!$this->isFull()){
					$this->meta += 2;
					if($this->meta > 0x06) $this->meta = 0x06;
					$tile->setPotionId($item->getDamage());
					$tile->setSplashPotion($item->getId() === Item::SPLASH_POTION);
					$tile->clearCustomColor();
					$this->getLevel()->setBlock($this, $this, true);
					if($player->isSurvival()){
						$player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
					}
					$color = Potion::getColor($item->getDamage())->toArray();
					$this->getLevel()->addSound(new SpellSound($this->add(0.5, 1, 0.5), $color[0], $color[1], $color[2]));
				}
				break;
			case Item::GLASS_BOTTLE:
				$player->getServer()->getPluginManager()->callEvent($ev = new PlayerGlassBottleEvent($player, $this, $item));
				if($ev->isCancelled()){
					return false;
				}
				if($this->meta < 2){
					break;
				}
				if($tile->hasPotion()){
					$this->meta -= 2;
					if($tile->getSplashPotion() === true){
						$result = Item::get(Item::SPLASH_POTION, $tile->getPotionId());
					}else{
						$result = Item::get(Item::POTION, $tile->getPotionId());
					}
					if($this->isEmpty()){
						$tile->setPotionId(-1);//reset
						$tile->setSplashPotion(false);
						$tile->clearCustomColor();
					}
					$this->getLevel()->setBlock($this, $this, true);
					$this->addItem($item, $player, $result);
					$color = Potion::getColor($result->getDamage())->toArray();
					$this->getLevel()->addSound(new SpellSound($this->add(0.5, 1, 0.5), $color[0], $color[1], $color[2]));
				}else{
					$this->meta -= 2;
					$this->getLevel()->setBlock($this, $this, true);
					if($player->isSurvival()){
						$result = Item::get(Item::POTION, Potion::WATER_BOTTLE);
						$this->addItem($item, $player, $result);
					}
					$this->getLevel()->addSound(new GraySplashSound($this->add(0.5, 1, 0.5)));
				}
				break;
		}
		return true;
	}

	public function addItem(Item $item, Player $player, Item $result){
		if($item->getCount() <= 1){
			$player->getInventory()->setItemInHand($result);
		}else{
			$item->setCount($item->getCount() - 1);
			if($player->getInventory()->canAddItem($result) === true){
				$player->getInventory()->addItem($result);
			}else{
				$motion = $player->getDirectionVector()->multiply(0.4);
				$position = clone $player->getPosition();
				$player->getLevel()->dropItem($position->add(0, 0.5, 0), $result, $motion, 40);
			}
		}
	}
}