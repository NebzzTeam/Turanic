<?php

/*
 *
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
 *
*/

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;


class BanCidCommand extends VanillaCommand {

	/**
	 * BanCidCommand constructor.
	 *
	 * @param string $name
	 */
	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.bancid.description",
			"%pocketmine.command.bancid.usage"
		);
		$this->setPermission("pocketmine.command.bancid");
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $currentAlias
	 * @param array         $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
            $sender->sendMessage($sender->getServer()->getLanguage()->translateString("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		$cid = array_shift($args);
		$reason = implode(" ", $args);

		$sender->getServer()->getCIDBans()->addBan($cid, $reason, null, $sender->getName());

		$player = null;

		foreach($sender->getServer()->getOnlinePlayers() as $p){
			if($p->getClientId() == $cid){
				$p->kick($reason !== "" ? "Banned by admin. Reason:" . $reason : "Banned by admin.");
				$player = $p;
				break;
			}
		}

		Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.bancid.success", [$player !== null ? $player->getName() : $cid]));

		return true;
	}
}
