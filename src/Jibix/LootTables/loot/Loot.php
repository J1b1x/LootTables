<?php
namespace Jibix\LootTables\loot;
use Closure;
use pocketmine\item\Armor;
use pocketmine\item\Item;


/**
 * Class Loot
 * @package Jibix\LootTables\loot
 * @author Jibix
 * @date 07.08.2023 - 03:15
 * @project LootTables
 */
class Loot{

    public static function equalsSelf(): Closure{
        return fn (Item $item, Item $loot): bool => !$item->equals($loot);
    }

    public static function equalsArmor(): Closure{
        return fn (Item $item, Item $loot): bool => !$loot instanceof Armor || !$item instanceof Armor || $item->getArmorSlot() !== $loot->getArmorSlot();
    }

    public function __construct(
        protected Item $item,
        protected int $chance,
        protected int $minCount = 1,
        protected int $maxCount = 1,
        protected ?Closure $isCompatible = null,
    ){}

    public function getItem(): Item{
        return $this->item;
    }

    public function getChance(): int{
        return $this->chance;
    }

    public function getCount(): int{
        return $this->minCount == $this->maxCount ? $this->minCount : mt_rand($this->minCount, $this->maxCount);
    }

    public function isCompatibleWith(Item $item): bool{
        return $this->isCompatible?->__invoke($item, $this->item) ?? true;
    }
}