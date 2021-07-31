<?php
declare(strict_types=1);

namespace NgLamVN\PvPCounter\command;

use jojoe77777\FormAPI\CustomForm;
use NgLamVN\PvPCounter\Loader;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;

class PvPCounterCommand extends PluginCommand
{
    private Loader $loader;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
        parent::__construct("pvpcounter", $loader);
        $this->setDescription("Manage PvPCounter Settings");
    }

    private function getLoader(): Loader
    {
        return $this->loader;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player)
        {
            $sender->sendMessage("Please use ingame");
            return;
        }
        $form = new CustomForm(function (Player $player, ?array $data)
        {
            if ($data == null) return;
            $stat = $this->getLoader()->getStatManager()->getPlayerPvPStat($player);
            $stat->setShowCps($data[0]);
            $stat->setShowCombo($data[1]);
            $stat->setShowReach($data[2]);
        });
        $data = $this->getLoader()->getStatManager()->getPlayerPvPStat($sender);

        $form->setTitle("PVPCounter Setting");
        $form->addToggle("Show CPS", $data->isShowCps());
        $form->addToggle("Show Combo", $data->isShowCombo());
        $form->addToggle("Show Reach", $data->isShowReach());

        $sender->sendForm($form);
    }
}