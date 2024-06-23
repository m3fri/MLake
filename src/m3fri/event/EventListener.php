<?php

declare(strict_types=1);

namespace m3fri\event;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use m3fri\MLake;
use m3fri\task\CheckPlayerTask;

class EventListener implements Listener {

	private MLake $plugin;
	private array $positions = [];

	public function __construct(MLake $plugin) {
		$this->plugin = $plugin;
	}

	public function onPlayerInteract(PlayerInteractEvent $event): void {
		$player = $event->getPlayer();
		$item = $event->getItem();
		$config = $this->plugin->getConfigData();
		$messages = $config->get("messages");
		$wand = $config->get("wand");
	
		if ($item->getCustomName() === $wand["name"]) {
			$block = $event->getBlock();
			if (!isset($this->positions[$player->getName()])) {
				$this->positions[$player->getName()] = ["min" => null, "max" => null];
			}
	
			if ($this->positions[$player->getName()]["min"] === null) {
				$this->positions[$player->getName()]["min"] = $block->asPosition();
				$message = str_replace(
					["{x}", "{y}", "{z}"],
					[$block->getX(), $block->getY(), $block->getZ()],
					$messages["set_position1"]
				);
				$player->sendMessage($message);
			} else {
				$this->positions[$player->getName()]["max"] = $block->asPosition();
				$message = str_replace(
					["{x}", "{y}", "{z}"],
					[$block->getX(), $block->getY(), $block->getZ()],
					$messages["set_position2"]
				);
				$player->sendMessage($message);
	
				$config->set("world", $player->getLevel()->getName());
				$config->set("minx", min($this->positions[$player->getName()]["min"]->getX(), $this->positions[$player->getName()]["max"]->getX()));
				$config->set("miny", min($this->positions[$player->getName()]["min"]->getY(), $this->positions[$player->getName()]["max"]->getY()));
				$config->set("minz", min($this->positions[$player->getName()]["min"]->getZ(), $this->positions[$player->getName()]["max"]->getZ()));
				$config->set("maxx", max($this->positions[$player->getName()]["min"]->getX(), $this->positions[$player->getName()]["max"]->getX()));
				$config->set("maxy", max($this->positions[$player->getName()]["min"]->getY(), $this->positions[$player->getName()]["max"]->getY()));
				$config->set("maxz", max($this->positions[$player->getName()]["min"]->getZ(), $this->positions[$player->getName()]["max"]->getZ()));
				$config->save();
			}
		}
	}
	

	public function onPlayerMove(PlayerMoveEvent $event): void {
		$player = $event->getPlayer();
		$config = $this->plugin->getConfigData();
		$messages = $config->get("messages");
		$playerName = $player->getName();

		if (
			$player->getLevel()->getName() === $config->get("world") &&
			$player->getPosition()->getX() >= $config->get("minx") && $player->getPosition()->getX() <= $config->get("maxx") &&
			$player->getPosition()->getY() >= $config->get("miny") && $player->getPosition()->getY() <= $config->get("maxy") &&
			$player->getPosition()->getZ() >= $config->get("minz") && $player->getPosition()->getZ() <= $config->get("maxz")
		) {
			if (!isset($this->plugin->playersInZone[$playerName])) {
				$this->plugin->playersInZone[$playerName] = time();
				$player->sendMessage($messages["enter_zone"]);
				$task = new CheckPlayerTask($this->plugin, $player);
				$taskId = $this->plugin->getServer()->getScheduler()->scheduleDelayedTask($task, 20)->getTaskId();
				$this->plugin->addTask($playerName, $taskId);
			}
		} else {
			if (isset($this->plugin->playersInZone[$playerName])) {
				unset($this->plugin->playersInZone[$playerName]);
				$player->sendMessage($messages["exit_zone"]);
				$this->plugin->cancelTaskByPlayer($playerName);
			}
		}
	}
}
