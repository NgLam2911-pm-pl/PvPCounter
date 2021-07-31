<?php
declare(strict_types=1);

namespace NgLamVN\PvPCounter;

use NgLamVN\PvPCounter\event\StatUpdateEvent;
use pocketmine\Player;
use pocketmine\Server;

class PlayerPvPStat
{
    protected Player $player;

    protected int $cps;
    protected float $reach; //Blocks but i think it can "float"
    protected int $combo;
    protected int $time;

    protected bool $show_cps;
    protected bool $show_combo;
    protected bool $show_reach;

    public function __construct(Player $player, bool $show_reach = true, bool $show_combo = true, bool $show_cps = true,  $base_cps = 0, $base_reach = 0, $base_combo = 0)
    {
        $this->player = $player;
        $this->combo = $base_combo;
        $this->reach = $base_reach;
        $this->cps = $base_cps;
        $this->show_combo = $show_combo;
        $this->show_cps = $show_cps;
        $this->show_reach = $show_reach;
        $this->time = time();
    }

    public function isShowCps(): bool
    {
        return $this->show_cps;
    }

    public function isShowReach(): bool
    {
        return $this->show_reach;
    }

    public function isShowCombo(): bool
    {
        return $this->show_combo;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getCps(): int
    {
        if($this->time !== time()){
            $this->time = time();
            $this->setCps(0);
        }
        return $this->cps;
    }

    public function getReach(): float
    {
        return $this->reach;
    }

    public function getCombo(): int
    {
        return $this->combo;
    }

    public function setCombo(int $value): void
    {
        $this->combo = $value;
        $this->save();
        $this->callEvent();
    }

    public function setCps(int $value): void
    {
        $this->cps = $value;
        $this->save();
        $this->callEvent();
    }

    public function setReach(float $value): void
    {
        $this->reach = $value;
        $this->save();
        $this->callEvent();
    }

    public function setReachSafe(float $value): void
    {
        $this->reach = $value;
        $this->save();
    }

    public function setComboSafe(int $value): void
    {
        $this->combo = $value;
        $this->save();
    }

    public function increaseCombo(): void
    {
        $this->setCombo($this->getCombo() + 1);
    }

    public function increaseCps(): void
    {
        $this->setCps($this->getCps() + 1);

        if($this->time !== time()){
            $this->time = time();
            $this->setCps(1);
        }
    }

    public function setShowCombo(bool $value): void
    {
        $this->show_combo = $value;
        $this->save();
    }

    public function setShowCps(bool $value): void
    {
        $this->show_cps = $value;
        $this->save();
    }

    public function setShowReach(bool $value): void
    {
        $this->show_reach = $value;
        $this->save();
    }

    public function callEvent(): void
    {
        $ev = new StatUpdateEvent($this->getPlayer());
        $ev->call();
    }
    public function save(): void
    {
        $loader = Server::getInstance()->getPluginManager()->getPlugin("PvPCounter");
        if ($loader instanceof Loader) $loader->getStatManager()->setPlayerPvPStat($this->getPlayer(), $this);
    }
}