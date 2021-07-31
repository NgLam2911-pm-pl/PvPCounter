<?php
declare(strict_types=1);

namespace NgLamVN\PvPCounter\provider;

use Exception;
use NgLamVN\PvPCounter\Loader;
use pocketmine\Player;
use pocketmine\Server;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use SOFe\AwaitGenerator\Await;
use Generator;

class SqliteProvider
{
    private Loader $loader;

    public const INIT = "pvpcounter.init";
    public const REGISTER = "pvpcounter.register";
    public const GET = "pvpcounter.get";
    public const REMOVE = "pvpcounter.remove";
    public const UPDATE_CPS = "pvpcounter.update.cps";
    public const UPDATE_COMBO = "pvpcounter.update.combo";
    public const UPDATE_REACH = "pvpcounter.update.reach";

    public DataConnector $database;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    public function getLoader(): Loader
    {
        return $this->loader;
    }

    public function init(): void
    {
        $this->getLoader()->getLogger()->info("Creating Database ... [SQLITE]");

        try
        {
            $this->database = libasynql::create($this->getLoader(), $this->getLoader()->getConfig()->get("database"), [
                "sqlite" => "sqlite.sql"
            ]);
        }
        catch (Exception $e)
        {
            $this->getLoader()->getLogger()->error("FAILED TO CREATE DATABASE, FORCE DISABLE THIS PLUGIN ...");
            Server::getInstance()->getPluginManager()->disablePlugin($this->getLoader());
        }

        $this->database->executeGeneric(self::INIT);
    }

    public function close(): void
    {
        if (isset($this->database))
        $this->database->close();
    }

    /**
     * @param string $query
     * @param array $args
     * @return Generator
     * @description Use for async select query.
     */
    public function asyncSelect(string $query, array $args = []): Generator
    {
        $this->database->executeSelect($query, $args, yield, yield Await::REJECT);

        return yield Await::ONCE;
    }

    public function updateReach(Player $player, bool $value)
    {
        $this->database->executeChange(self::UPDATE_REACH, [
            "player" => $player->getName(),
            "value" => (int) $value
        ]);
    }

    public function updateCombo(Player $player, bool $value)
    {
        $this->database->executeChange(self::UPDATE_COMBO, [
            "player" => $player->getName(),
            "value" => (int) $value
        ]);
    }

    public function updateCps(Player $player, bool $value)
    {
        $this->database->executeChange(self::UPDATE_CPS, [
            "player" => $player->getName(),
            "value" => (int) $value
        ]);
    }

    public function register(Player $player, bool $cps = true, bool $reach = true, bool $combo = true): void
    {
        $this->database->executeChange(self::REGISTER, [
            "player" => $player->getName(),
            "cps" => (int) $cps,
            "reach" => (int) $reach,
            "combo" => (int) $combo
        ]);
    }

}