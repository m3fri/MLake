<?php

declare(strict_types=1);

namespace m3fri;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use m3fri\event\EventListener;
use m3fri\command\WandCommand;
use m3fri\command\ReloadCommand;

class MLake extends PluginBase {

	public $playersInZone;
	private Config $config;
	private $tasks = [];

	public function onEnable(): void {
		$this->saveDefaultConfig();

		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

		$this->getServer()->getCommandMap()->register("ml", new WandCommand($this));
		$this->getServer()->getCommandMap()->register("ml", new ReloadCommand($this));

		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	public function getConfigData(): Config {
		return $this->config;
	}

	public function reloadConfigData(): void {
		$this->reloadConfig();
		$this->config = $this->getConfig();
	}

	public function addTask(string $playerName, int $taskId): void {
		$this->tasks[$playerName] = $taskId;
	}

	public function cancelTaskByPlayer(string $playerName): void {
		if (isset($this->tasks[$playerName])) {
			$this->getServer()->getScheduler()->cancelTask($this->tasks[$playerName]);
			unset($this->tasks[$playerName]);
		}
	}
}
