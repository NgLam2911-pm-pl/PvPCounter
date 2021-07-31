<?php
declare(strict_types=1);

namespace NgLamVN\PvPCounter;

use pocketmine\Player;

class PlayerPvPStatManager
{
    private Loader $plugin;
    /** @var PlayerPvPStat[] */
    private array $player_data = [];

    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getPlugin(): Loader
    {
        return $this->plugin;
    }

    public function register(Player $player, bool $show_reach = true, bool $show_combo = true, bool $show_cps = true): void
    {
        $this->setPlayerPvPStat($player, new PlayerPvPStat($player, $show_reach, $show_combo, $show_cps));
    }

    public function setPlayerPvPStat(Player $player, PlayerPvPStat $stat): void
    {
        $this->player_data[$player->getName()] = $stat;
    }

    public function getPlayerPvPStat(Player $player): ?PlayerPvPStat
    {
        if (isset($this->player_data[$player->getName()]))
        {
            return $this->player_data[$player->getName()];
        }
        else return null;
    }

    public function removePlayerPvpStat(Player $player): void
    {
        if (isset($this->player_data[$player->getName()]))
            unset($this->player_data[$player->getName()]);
    }

    /**
     * @return PlayerPvPStat[]
     */
    public function getAllPlayerPvPStat(): array
    {
        if (isset($this->player_data)) return $this->player_data;
        return [];
    }
}
