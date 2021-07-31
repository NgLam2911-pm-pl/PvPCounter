<?php
declare(strict_types=1);

namespace NgLamVN\PvPCounter\event;

use NgLamVN\PvPCounter\Loader;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\Player;
use pocketmine\Server;

class StatUpdateEvent extends PluginEvent
{
    protected Player $player;

    public function __construct(Player $player)
    {
        parent::__construct($this->getLoader());
        $this->player = $player;
    }
    private function getLoader(): ?Loader
    {
        $loader = Server::getInstance()->getPluginManager()->getPlugin("PvPCounter");
        if ($loader instanceof Loader) return $loader;
        return null;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }
}