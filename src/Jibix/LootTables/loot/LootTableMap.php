<?php
namespace Jibix\LootTables\loot;
use Jibix\LootTables\util\Utils;
use pocketmine\inventory\Inventory;


/**
 * Class LootTableMap
 * @package Jibix\LootTables\loot
 * @author Jibix
 * @date 07.08.2023 - 03:15
 * @project LootTables
 */
class LootTableMap{

    /**
     * LootTableMap constructor.
     * @param string $name
     * @param LootTable[] $lootTables
     */
    public function __construct(protected string $name, protected array $lootTables){}

    public function getName(): string{
        return $this->name;
    }

    public function getLootTables(): array{
        return $this->lootTables;
    }

    /**
     * Function spread
     * @param float $fillMultiplier
     * @param Inventory[] $inventories
     * @param bool $shuffle (recommend setting this to true when you spread it the first time, so it looks a bit more randomized)
     * @return void
     */
    public function spread(float $fillMultiplier, array $inventories, bool $shuffle = false): void{
        if (!$inventories) return;
        $itemsPerInventory = round((array_sum(array_map(fn (Inventory $inventory): int => $inventory->getSize(), $inventories)) / count($inventories)) * $fillMultiplier);
        $invs = $counts = [];
        foreach ($inventories as $inventory) {
            $invs[$id = spl_object_id($inventory)] = $inventory;
            $counts[$id] = 0;
        }

        foreach ($this->generateLoots($this->lootTables, $itemsPerInventory) as $loot) {
            asort($counts);
            foreach (array_keys($counts) as $id) {
                $inventory = $invs[$id];
                if ($inventory->canAddItem($item = $loot->getItem())) {
                    $inventory->addItem($item);
                    $counts[$id]++;
                    continue 2;
                }
            }
        }
        if ($shuffle) return;
        foreach ($invs as $inventory) {
            Utils::shuffleInventory($inventory);
        }
    }

    /**
     * Function generateLoots
     * @param LootTable[] $tables
     * @param int $amount
     * @param Loot[] $loots
     * @param int|null $initial
     * @param int $attempts
     * @return array
     */
    protected function generateLoots(array $tables, int $amount, array &$loots = [], ?int $initial = null, int $attempts = 10): array{
        if ($attempts <= 0) return $loots;
        //TODO: Make this async!
        $initial ??= $amount;
        foreach ($this->lootTables as $i => $table) {
            foreach ($table->generateLoots($loots) as $loot) {
                $loots[] = $loot;
            }
            if ($table->canGenerateOnce()) unset($tables[$i]);
        }
        if (count($loots) < $initial) return self::generateLoots($tables, $amount - count($loots), $loots, $initial, --$attempts);
        shuffle($loots);
        return array_slice($loots, 0, $initial);
    }
}