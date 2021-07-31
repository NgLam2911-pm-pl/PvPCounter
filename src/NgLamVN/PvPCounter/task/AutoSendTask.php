<?php
declare(strict_types=1);

namespace NgLamVN\PvPCounter\task;

use NgLamVN\PvPCounter\Loader;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class AutoSendTask extends Task
{
    private Loader $loader;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    private function getLoader(): Loader
    {
        return $this->loader;
    }

    public function onRun(int $currentTick)
    {
        $players = Server::getInstance()->getOnlinePlayers();

        foreach ($players as $player)
            $this->getLoader()->sendStatPopup($player);
    }
}