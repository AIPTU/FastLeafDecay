<?php

/*
 * Copyright (c) 2024 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/FastLeafDecay
 */

declare(strict_types=1);

namespace aiptu\fastleafdecay;

use pocketmine\block\Leaves;
use pocketmine\block\Wood;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\Listener;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\World;
use SplQueue;
use function is_int;
use function mt_rand;

class FastLeafDecay extends PluginBase implements Listener {
	private int $minLeafDecayDelay;
	private int $maxLeafDecayDelay;
	private int $maxSearchRadius;

	public function onEnable() : void {
		$this->saveDefaultConfig();
		$this->validateConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	private function validateConfig() : void {
		$config = $this->getConfig();

		$minDelay = $config->get('min_leaf_decay_delay', 1);
		if (!is_int($minDelay) || $minDelay < 1) {
			$this->getLogger()->warning('min_leaf_decay_delay is invalid, setting to default value of 1.');
			$this->minLeafDecayDelay = 1;
		} else {
			$this->minLeafDecayDelay = $minDelay;
		}

		$maxDelay = $config->get('max_leaf_decay_delay', 5);
		if (!is_int($maxDelay) || $maxDelay < $this->minLeafDecayDelay) {
			$this->getLogger()->warning('max_leaf_decay_delay is invalid or less than min_leaf_decay_delay, setting to min_leaf_decay_delay.');
			$this->maxLeafDecayDelay = $this->minLeafDecayDelay;
		} else {
			$this->maxLeafDecayDelay = $maxDelay;
		}

		$radius = $config->get('max_search_radius', 6);
		if (!is_int($radius) || $radius < 1) {
			$this->getLogger()->warning('max_search_radius is invalid, setting to default value of 6.');
			$this->maxSearchRadius = 6;
		} else {
			$this->maxSearchRadius = $radius;
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void {
		$block = $event->getBlock();
		if ($block instanceof Wood) {
			$this->scheduleLeafDecay($block->getPosition()->getWorld(), $block->getPosition());
		}
	}

	private function scheduleLeafDecay(World $world, Vector3 $origin) : void {
		$queue = new SplQueue();
		$visited = [];

		$this->enqueuePosition($queue, $visited, $origin);

		while (!$queue->isEmpty()) {
			/** @var Vector3 $current */
			$current = $queue->dequeue();

			foreach (Facing::ALL as $direction) {
				$neighbor = $current->getSide($direction);
				$hash = World::blockHash($neighbor->getFloorX(), $neighbor->getFloorY(), $neighbor->getFloorZ());

				if (!isset($visited[$hash]) && $this->isWithinRadius($origin, $neighbor)) {
					$visited[$hash] = true;
					$block = $world->getBlock($neighbor);

					if ($block instanceof Wood) {
						continue;
					}

					if ($block instanceof Leaves) {
						if ($block->isNoDecay() && !$block->isCheckDecay()) {
							continue;
						}

						$ev = new LeavesDecayEvent($block);
						$ev->call();

						if (!$ev->isCancelled()) {
							$this->scheduleLeafBreak($world, $neighbor);
						}

						$this->enqueuePosition($queue, $visited, $neighbor);
					}
				}
			}
		}
	}

	private function scheduleLeafBreak(World $world, Vector3 $position) : void {
		$delay = mt_rand($this->minLeafDecayDelay, $this->maxLeafDecayDelay) * 20;
		$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($world, $position) : void {
			$world->useBreakOn($position);
		}), $delay);
	}

	private function enqueuePosition(SplQueue $queue, array &$visited, Vector3 $position) : void {
		$queue->enqueue($position);
		$visited[World::blockHash($position->getFloorX(), $position->getFloorY(), $position->getFloorZ())] = true;
	}

	private function isWithinRadius(Vector3 $origin, Vector3 $position) : bool {
		return $origin->distance($position) <= $this->maxSearchRadius;
	}
}
