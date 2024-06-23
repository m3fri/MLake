<?php

declare(strict_types=1);

namespace m3fri\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\ItemIds;
use pocketmine\Player;
use pocketmine\item\Item;
use m3fri\MLake;

class WandCommand extends Command {

    private MLake $plugin;

    public function __construct(MLake $plugin) {
        parent::__construct("ml wand", "Получить волшебный топор", "/ml wand");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $label, array $args): void
    {
        $config = $this->plugin->getConfigData();
        $messages = $config->get("messages");
		$wand = $config->get("wand");

        if ($sender instanceof Player) {
            $item = Item::get(ItemIds::WOODEN_AXE);
            $item->setCustomName($wand["name"]);
            $sender->getInventory()->addItem($item);
            $sender->sendMessage($messages["give_wand"]);
        } else {
            $sender->sendMessage($messages["command_only_in_game"]);
        }
    }
}
