# LootTables

![php](https://img.shields.io/badge/php-8.1-informational)
![api](https://img.shields.io/badge/pocketmine-5.0-informational)

LootTables is a PocketMine-MP library for spreading certain loots in inventories.
This can be used for mini-games, such as SkyWars, JumpLeague etc.

## Categories
+ [Loot](#loot)
  + [Construction](#loot-construction)
  + [Compatibilities](#compatibilities)
+ [LootTable](#item-registration)
  + [Construction](#loottable-construction)
  + [Loot generation](#loot-generating)
+ [LootTableMap](#functional-item)
  + [Construction](#loottablemap-construction)
  + [Spreading](#spreading)
+ [Use example](#use-example)

## Loot
### Loot construction
```php
public function __construct(
    Item $item, //The loots item
    int $chance, //The loots chance of being generated
    int $minCount = 1, //The min count of the item
    int $maxCount = 1, //The max count of the item
    ?Closure $isCompatible = null, //Compatibility with other items
){}
```
### Compatibilities
```php
Loot::equalsSelf() //Checks if the generated item does not equal the loot item (so the loot can't be generated twice) 
Loot::equalsArmor() //Checks if the generated item does not have the same armor slot as the loot item (so it won't generate multiple armor pieces with the same slot, like chestplates for example)

//You can also make your own compatibility function, just like this
function (Item $generated, Item $lootItem): bool{
    return true; //Put your own check in here
} 
```

## LootTable
### LootTable construction
```php
public function __construct(
    int $minLootAmount, //The min amount of loots this table can generate
    int $maxLootAmount, //The max amount of loots this table can generate
    bool $generateOnce, //Weather the table can generate loots multiple times
    array $loots //The array of Loot objects for this table
){}
```
### Loot generating
This is actually supposed to be used by the LootTableMap only
```php
//$loots is the array of loots that got generated already
//$amount is the amount of loots that get generated, use null to make it a random amount between $minLootAmount and $maxLootAmount
public function generateLoots(array &$loots = [], ?int $amount = null): Generator{}
```

## LootTableMap
### LootTableMap construction
```php
public function __construct(
    string $name, //The name of the loot table map (for example "easy", "medium", "hard")
    array $lootTables //The array of LootTables 
){}
```

### Spreading
```php
//$fillMultiplier is a percentage value of how much the inventory is gonna get filled
//$inventories is the array of inventory objects where the loots get put in
//$shuffle is weather the inventory contents gonna get shuffled (use this when you fill the invs for the first time to make the loots look more randomized)
public function spread(float $fillMultiplier, array $inventories, bool $shuffle = false): void{}
```

## Use example
### This is an example of how to use this library for mini-games like SkyWars
```php
class Example{
    
    private const FILL_MULTIPLIER = 0.4;
    private const MULTIPLIER_RANDOMIZER = 0.2;
  
    private LootTable $table;
  
    public function initialize(): void{
        $this->table = new LootTable(
            new Loot(VanillaBlocks::OAK_PLANKS()->asItem(), 70, 20, 50),
            new Loot(VanillaBlocks::STONE()->asItem(), 70, 20, 50),
            new Loot(VanillaBlocks::BRICKS()->asItem(), 65, 20, 50),

            new Loot(VanillaItems::APPLE(), 55, 1, 3),
            new Loot(VanillaItems::BREAD(), 55, 1, 4),
            new Loot(VanillaBlocks::CAKE()->asItem(), 30),
            new Loot(VanillaItems::RAW_FISH(), 55, 1, 3),
            new Loot(VanillaItems::COOKED_FISH(), 50, 1, 3),
            new Loot(VanillaItems::RAW_CHICKEN(), 55, 1, 3),
            new Loot(VanillaItems::COOKED_CHICKEN(), 50, 1, 3),

            new Loot(VanillaItems::GOLDEN_APPLE(), 2, 1, 2),

            new Loot(VanillaItems::EGG(), 40, 1, 16),
            new Loot(VanillaItems::SNOWBALL(), 40, 1, 16),
            
            new Loot(VanillaItems::STONE_SWORD(), 27, 1, 1, Loot::equalsSelf()), //Only one stone sword per "island".
            new Loot(VanillaItems::IRON_HELMET(), 20, 1, 1, Loot::equalsArmor()), //Only one helmet per "island".
        );
    }
  
    /**
     * Function spread
     * @param Inventory[] $inventories
     * @param bool $shuffle
     * @return void
     */
    public function spread(array $inventories, bool $shuffle = true): void{
        $this->table->spread(
            self::FILL_MULTIPLIER + random_float(-self::MULTIPLIER_RANDOMOIZER, self::MULTIPLIER_RANDOMOIZER,
            $inventories,
            $shuffle
        ),
    }
}
```