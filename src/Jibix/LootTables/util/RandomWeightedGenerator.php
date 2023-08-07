<?php
namespace Jibix\LootTables\util;
use pocketmine\utils\Binary;
use pocketmine\utils\Random;


/**
 * Class RandomWeightedGenerator
 * @package Jibix\LootTables\util
 * @author Muqsit (https://gist.github.com/Muqsit/5042779c0e87fd55e55560f83e24af69)
 * @date 07.08.2023 - 03:23
 * @project LootTables
 */
class RandomWeightedGenerator{

    private Random $random;

    private array $probabilities = [];
    private array $aliases;
    private array $indexes = [];

    public function add(mixed $value, float $weight): void{
        $this->probabilities[] = $weight;
        $this->indexes[] = $value;
    }

    public function count(): int{
        return count($this->probabilities);
    }

    private function normalize(): void{
        $sum = array_sum($this->probabilities);
        foreach ($this->probabilities as &$weight) {
            $weight /= $sum;
        }
    }

    public function setup(): void{
        $count = $this->count();
        if ($count == 0) return;

        // Store the underlying generator.
        $this->random = new Random(Binary::readLong(Binary::writeInt(mt_rand()) . Binary::writeInt(mt_rand())));
        $this->aliases = [];

        $this->normalize();

        // Compute the average probability and cache it for later use.
        $average = 1 / $count;

        $probabilities = $this->probabilities;

        // Create two stacks to act as worklists as we populate the tables.
        $small = [];
        $large = [];

        // Populate the stacks with the input probabilities.
        for ($i = 0; $i < $count; ++$i) {
            /**
             * If the probability is below the average probability, then we add
             * it to the small list; otherwise we add it to the large list.
             */
            $probabilities[$i] >= $average ? $large[] = $i : $small[] = $i;
        }

        /**
         * As a note: in the mathematical specification of the algorithm, we
         * will always exhaust the small list before the big list.  However,
         * due to floating point inaccuracies, this is not necessarily true.
         * Consequently, this inner loop (which tries to pair small and large
         * elements) will have to check that both lists aren't empty.
         */
        while (count($small) > 0 && count($large) > 0) {
            /* Get the index of the small and the large probabilities. */
            $less = array_pop($small);
            $more = array_pop($large);

            /**
             * These probabilities have not yet been scaled up to be such that
             * 1/n is given weight 1.0.  We do this here instead.
             */
            $this->probabilities[$less] = $probabilities[$less] * $count;
            $this->aliases[$less] = $more;

            /**
             * Decrease the probability of the larger one by the appropriate
             * amount.
             */
            $probabilities[$more] = ($probabilities[$more] + $probabilities[$less]) - $average;

            /**
             * If the new probability is less than the average, add it into the
             * small list; otherwise add it to the large list.
             */
            $probabilities[$more] >= 1.0 / $count ? $large[] = $more : $small[] = $more;
        }

        /**
         * At this point, everything is in one list, which means that the
         * remaining probabilities should all be 1/n.  Based on this, set them
         * appropriately.  Due to numerical issues, we can't be sure which
         * stack will hold the entries, so we empty both.
         */
        while (count($small) > 0) {
            $this->probabilities[array_pop($small)] = 1;
        }
        while (count($large) > 0) {
            $this->probabilities[array_pop($large)] = 1;
        }
    }

    private function generateIndexes(int $count): \Generator{
        $probabilities = count($this->probabilities);
        if ($probabilities > 0) {
            while (--$count >= 0) {
                $index = $this->random->nextBoundedInt($probabilities);
                yield $this->random->nextFloat() <= $this->probabilities[$index] ? $index : $this->aliases[$index];
            }
        }
    }

    public function generate(int $count): \Generator{
        foreach ($this->generateIndexes($count) as $index) {
            yield $this->indexes[$index];
        }
    }
}