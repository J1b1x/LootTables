<?php
namespace Jibix\LootTables\util;
use pocketmine\inventory\Inventory;


/**
 * Class Utils
 * @package Jibix\LootTables\util
 * @author Jibix
 * @date 07.08.2023 - 03:19
 * @project LootTables
 */
final class Utils{

    public static function shuffleInventory(Inventory $inventory): void{
        $slots = range(0, $inventory->getSize());
        shuffle($slots);
        $result = [];
        foreach (array_values($inventory->getContents()) as $i => $content) {
            $result[$slots[$i]] = $content;
        }
        $inventory->clearAll();
        $inventory->setContents($result);
    }
}