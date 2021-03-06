<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


use pocketmine\math\Vector3;

class MovePlayerPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::MOVE_PLAYER_PACKET;

	const MODE_NORMAL = 0;
	const MODE_RESET = 1;
	const MODE_TELEPORT = 2;
	const MODE_ROTATION = 3, MODE_PITCH = 3;

    /** @var int */
    public $entityRuntimeId;
    /** @var Vector3 */
    public $position;
    /** @var float */
    public $pitch;
    /** @var float */
    public $yaw;
    /** @var float */
    public $headYaw;
    /** @var int */
    public $mode = self::MODE_NORMAL;
    /** @var bool */
    public $onGround = false; //TODO
    /** @var int */
    public $ridingEid = 0;
    /** @var int */
    public $int1 = 0;
    /** @var int */
    public $int2 = 0;

    protected function decodePayload(){
        $this->entityRuntimeId = $this->getEntityRuntimeId();
        $this->position = $this->getVector3Obj();
        $this->pitch = $this->getLFloat();
        $this->yaw = $this->getLFloat();
        $this->headYaw = $this->getLFloat();
        $this->mode = $this->getByte();
        $this->onGround = $this->getBool();
        $this->ridingEid = $this->getEntityRuntimeId();
        if($this->mode === MovePlayerPacket::MODE_TELEPORT){
            $this->int1 = $this->getLInt();
            $this->int2 = $this->getLInt();
        }
    }

	protected function encodePayload(){
        $this->putEntityRuntimeId($this->entityRuntimeId);
        $this->putVector3Obj($this->position);
        $this->putLFloat($this->pitch);
        $this->putLFloat($this->yaw);
        $this->putLFloat($this->headYaw);
        $this->putByte($this->mode);
        $this->putBool($this->onGround);
        $this->putEntityRuntimeId($this->ridingEid);
        if($this->mode === MovePlayerPacket::MODE_TELEPORT){
            $this->putLInt($this->int1);
            $this->putLInt($this->int2);
        }
	}
}