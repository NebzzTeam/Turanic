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

namespace pocketmine\nbt;

#ifndef COMPILE
use pocketmine\utils\Binary;
#endif

#include <rules/NBT.h>

class BigEndianNBTStream extends NBTStream{

    public function getShort() : int{
        return Binary::readShort($this->get(2));
    }

    public function getSignedShort() : int{
        return Binary::readSignedShort($this->get(2));
    }

    public function putShort(int $v){
        $this->buffer .= Binary::writeShort($v);
    }

    public function getInt() : int{
        return Binary::readInt($this->get(4));
    }

    public function putInt(int $v){
        $this->buffer .= Binary::writeInt($v);
    }

    public function getLong() : int{
        return Binary::readLong($this->get(8));
    }

    public function putLong(int $v){
        $this->buffer .= Binary::writeLong($v);
    }

    public function getFloat() : float{
        return Binary::readFloat($this->get(4));
    }

    public function putFloat(float $v){
        $this->buffer .= Binary::writeFloat($v);
    }

    public function getDouble() : float{
        return Binary::readDouble($this->get(8));
    }

    public function putDouble(float $v){
        $this->buffer .= Binary::writeDouble($v);
    }

    public function getIntArray() : array{
        $len = $this->getInt();
        return array_values(unpack("N*", $this->get($len * 4)));
    }

    public function putIntArray(array $array){
        $this->putInt(count($array));
        $this->put(pack("N*", ...$array));
    }
}