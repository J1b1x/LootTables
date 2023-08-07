<?php
namespace Jibix\LootTables\loot;
use Generator;
use Jibix\LootTables\util\RandomWeightedGenerator;


/**
 * Class LootTable
 * @package Jibix\LootTables\loot
 * @author Jibix
 * @date 07.08.2023 - 03:15
 * @project LootTables
 */
class LootTable{

    private RandomWeightedGenerator $generator;

    /**
     * LootTable constructor.
     * @param int $minLootAmount
     * @param int $maxLootAmount
     * @param bool $generateOnce
     * @param Loot[] $loots
     */
    public function __construct(
        protected int $minLootAmount,
        protected int $maxLootAmount,
        protected bool $generateOnce,
        array $loots
    ){
        $this->generator = new RandomWeightedGenerator();
        foreach ($loots as $loot) {
            $this->generator->add($loot, $loot->getChance());
        }
        $this->generator->setup();
    }

    public function canGenerateOnce(): bool{
        return $this->generateOnce;
    }

    /**
     * Function generateLoots
     * @param Loot[] $loots
     * @param int|null $amount
     * @return Generator
     */
    public function generateLoots(array &$loots = [], ?int $amount = null): Generator{
        $amount ??= mt_rand($this->minLootAmount, $this->maxLootAmount);
        if ($amount <= 0) return;
        /** @var Loot $newLoot */
        foreach ($this->generator->generate($amount) as $newLoot) {
            $item = $newLoot->getItem()->setCount($newLoot->getCount());
            foreach ($loots as $loot) {
                if (!$loot->isCompatibleWith($item)) continue 2;
            }
            $amount--;
            yield $newLoot;
        }
        if ($amount > 0) $this->generateLoots($loots, $amount);
    }
}