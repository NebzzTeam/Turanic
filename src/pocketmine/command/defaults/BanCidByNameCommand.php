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
use pocketmine\Player;

class BanCidByNameCommand extends VanillaCommand {

	/**
	 * BanCidByNameCommand constructor.
	 *
	 * @param string $name
	 */
	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.bancidbyname.description",
			"%pocketmine.command.bancidbyname.usage"
		);
		$this->setPermission("pocketmine.command.bancidbyname");
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $currentAlias
	 * @param array         $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->canExecute($sender)){
			return true;
		}

		if(count($args) === 0){
            $sender->sendMessage($sender->getServer()->getLanguage()->translateString("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		$name = array_shift($args);
		$reason = implode(" ", $args);

		if($sender->getServer()->getPlayer($name) instanceof Player) $target = $sender->getServer()->getPlayer($name);
		else return false;

		$sender->getServer()->getCIDBans()->addBan($target->getClientId(), $reason, null, $sender->getName());

		$target->kick($reason !== "" ? "Banned by admin. Reason:" . $reason : "Banned by admin.");

		Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.bancidbyname.success", [$target !== null ? $target->getName() : $name]));

		return true;
	}
}
