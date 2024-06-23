<?php

declare(strict_types=1);

namespace m3fri\command;

use m3fri\MLake;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class ReloadCommand extends Command {

    private MLake $plugin;

    public function __construct(MLake $plugin) {
        parent::__construct("ml reload", "Перезагрузить конфигурацию", "/ml reload");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $label, array $args): void
    {
        $this->plugin->reloadConfigData();
        $config = $this->plugin->getConfigData();
        $messages = $config->get("messages");
        $sender->sendMessage($messages["reload_config"]);
    }
}
