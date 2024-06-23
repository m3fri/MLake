<?php

declare(strict_types=1);

namespace m3fri\task;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use m3fri\MLake;
use DWEco\Economy;

class CheckPlayerTask extends Task {

    private MLake $plugin;
    private Player $player;
    private int $startTime;

    public function __construct(MLake $plugin, Player $player) {
        $this->plugin = $plugin;
        $this->player = $player;
        $this->startTime = time();
    }

	public function onRun(int $currentTick): void {
        $config = $this->plugin->getConfigData();
        $messages = $config->get("messages");

        if (
            $this->player->getLevel()->getName() === $config->get("world") &&
            $this->player->getPosition()->getX() >= $config->get("minx") && $this->player->getPosition()->getX() <= $config->get("maxx") &&
            $this->player->getPosition()->getY() >= $config->get("miny") && $this->player->getPosition()->getY() <= $config->get("maxy") &&
            $this->player->getPosition()->getZ() >= $config->get("minz") && $this->player->getPosition()->getZ() <= $config->get("maxz")
        ) {
            $elapsedTime = time() - $this->startTime;
            $timeRequired = $config->get("time");

            if ($elapsedTime >= $timeRequired) {
                Economy::getInstance()->addMoney($this->player, $config->get("money"));
                $message = str_replace("{money}", (string)$config->get("money"), $messages["reward"]);
                $this->player->sendMessage($message);
                $this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId());
            } else {
                $timeLeft = $timeRequired - $elapsedTime;
                $minutes = intdiv($timeLeft, 60);
                $seconds = $timeLeft % 60;
                $message = str_replace(["{minutes}", "{seconds}"], [$minutes, $seconds], $messages["time_left"]);
                $this->player->sendTip($message);
            }
        } else {
            $this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId());
        }
    }

    public function getPlayer(): Player {
        return $this->player;
    }
}
