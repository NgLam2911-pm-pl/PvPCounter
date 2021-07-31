<?php
declare(strict_types=1);

namespace NgLamVN\PvPCounter;

use NgLamVN\PvPCounter\command\PvPCounterCommand;
use NgLamVN\PvPCounter\provider\SqliteProvider;
use NgLamVN\PvPCounter\task\AutoSendTask;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use SOFe\AwaitGenerator\Await;

class Loader extends PluginBase
{
    //TODO: CONFIG
    //TODO: REACH Caculator
    //TODO: Combo caculator
    //TODO: CPS Caculator

    /** @var int[] */
    public array $rstime = [];
    /** @var int[] */
    public array $cstime = [];

    private PlayerPvPStatManager $statManager;

    private SqliteProvider $provider;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->statManager = new PlayerPvPStatManager($this);
        $this->provider = new SqliteProvider($this);
        $this->provider->init();

        $this->getServer()->getCommandMap()->register("pvpcounter", new PvPCounterCommand($this));
        $this->getScheduler()->scheduleRepeatingTask(new AutoSendTask($this), 2*20);
    }

    public function onDisable()
    {
        $this->getProvider()->close();
    }

    public function getProvider(): SqliteProvider
    {
        return $this->provider;
    }

    public function getStatManager(): PlayerPvPStatManager
    {
        return $this->statManager;
    }

    public function loadPlayer(Player $player): void
    {
        Await::f2c(function () use ($player)
        {
            $data = yield $this->getProvider()->asyncSelect(SqliteProvider::GET, [
                "player" => $player->getName()
            ]);
            if (empty($data))
            {
                $this->getProvider()->register($player);
            }
            $data = yield $this->getProvider()->asyncSelect(SqliteProvider::GET, [
                "player" => $player->getName()
            ]);
            $stat = $data[0];
            $this->getStatManager()->register($player, (bool)$stat["Reach"], (bool)$stat["Combo"], (bool)$stat["Cps"]);
        });
    }

    public function unloadPlayer(Player $player): void
    {
        $data = $this->getStatManager()->getPlayerPvPStat($player);

        $this->getProvider()->updateCps($player, $data->isShowCps());
        $this->getProvider()->updateReach($player, $data->isShowReach());
        $this->getProvider()->updateCombo($player, $data->isShowCombo());

        $this->getStatManager()->removePlayerPvpStat($player);
    }

    public function sendStatPopup(Player $player): void
    {
        $popup = "";
        $data = $this->getStatManager()->getPlayerPvPStat($player);
        if ($data == null) return;

        if (isset($this->rstime[$player->getName()]))
        {
            if ($this->rstime[$player->getName()] + 2 < time())
            {
                $data->setReachSafe(0);
                $this->rstime[$player->getName()] = time();
            }
        }
        if (isset($this->cstime[$player->getName()]))
        {
            if ($this->cstime[$player->getName()] + 6 < time())
            {
                $data->setComboSafe(0);
                $this->cstime[$player->getName()] = time();
            }
        }

        $cps = " CPS: " . $data->getCps();
        $reach = " Reach: " . round($data->getReach(), 2, PHP_ROUND_HALF_DOWN);
        $combo = " Combo: " . $data->getCombo();

        if ($data->isShowCps()) $popup = $popup . $cps;
        if ($data->isShowCombo()) $popup = $popup . $combo;
        if ($data->isShowReach()) $popup = $popup . $reach;
        if ($popup == "") return;

        $player->sendPopup($popup);
    }
}