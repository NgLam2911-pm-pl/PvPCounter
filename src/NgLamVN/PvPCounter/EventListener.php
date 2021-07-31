<?php
declare(strict_types=1);

namespace NgLamVN\PvPCounter;

use NgLamVN\PvPCounter\event\StatUpdateEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\Player;

class EventListener implements Listener
{
    private Loader $plugin;

    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getPlugin(): Loader
    {
        return $this->plugin;
    }

    /**
     * @param PlayerJoinEvent $event
     * @priority HIGHEST
     * @ignoreCancelled TRUE
     */
    public function onJoin (PlayerJoinEvent $event)
    {
        $this->getPlugin()->loadPlayer($event->getPlayer());
    }

    /**
     * @param PlayerQuitEvent $event
     * @priority HIGHEST
     * @ignoreCancelled TRUE
     *
     * I know player quit event is uncancelable so ... that this the fking think ??
     */
    public function onQuit (PlayerQuitEvent $event)
    {
        $this->getPlugin()->unloadPlayer($event->getPlayer());
    }

    /**
     * @param DataPacketReceiveEvent $event
     * @priority HIGHEST
     * @ignoreCancelled TRUE
     * @description CPS Handle
     */
    public function onRecieve(DataPacketReceiveEvent $event)
    {
        $player = $event->getPlayer();
        $packet = $event->getPacket();

        if (($packet instanceof LevelSoundEventPacket and $packet->sound == LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE) or ($packet instanceof InventoryTransactionPacket and $packet->trData instanceof UseItemOnEntityTransactionData))
        {
            $this->getPlugin()->getStatManager()->getPlayerPvPStat($player)->increaseCps();
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     * @priority HIGHEST
     * @ignoreCancelled TRUE
     * @description Reach and Combo Handle
     */
    public function onHit(EntityDamageByEntityEvent $event)
    {
        $damager = $event->getDamager();
        $victim = $event->getEntity();

        if ($damager instanceof Player)
        {
            //ADD COMBO
            $this->getPlugin()->getStatManager()->getPlayerPvPStat($damager)->increaseCombo();

            //REACH CACULATION
            //WARNING: It not actually 100% correct so i am trying the best way to caculate it !
            $damagerhead_vector3 = $damager->asVector3();
            $damagerhead_vector3->y += $damager->getEyeHeight();

            if ($damagerhead_vector3->y <= $victim->asVector3()->y)
            {
                $victim_nearest_vector3 = new Vector3($victim->x, $damagerhead_vector3->y, $victim->z);
                $reach = $damagerhead_vector3->distance($victim_nearest_vector3);
            }
            else
            {
                $reach = $damagerhead_vector3->distance($victim->asVector3());
            }
            // OK, its better way... but not actually true.

            $this->getPlugin()->getStatManager()->getPlayerPvPStat($damager)->setReach($reach);
            $this->getPlugin()->rstime[$damager->getName()] = time();
            $this->getPlugin()->cstime[$damager->getName()] = time();
        }
        if ($victim instanceof Player)
        {
            //RESET COMBO
            $this->getPlugin()->getStatManager()->getPlayerPvPStat($victim)->setCombo(0);
        }
    }

    /**
     * @param StatUpdateEvent $event
     * @priority NORMAL
     * @ignoreCancelled TRUE
     * @description Handle when stat update.
     */
    public function onStatUpdate(StatUpdateEvent $event)
    {
        $player = $event->getPlayer();
        $this->getPlugin()->sendStatPopup($player);
    }
}