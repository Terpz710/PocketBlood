<?php

declare(strict_types=1);

namespace terpz710\blood;

use pocketmine\plugin\PluginBase;

use pocketmine\player\Player;

use pocketmine\world\particle\BlockBreakParticle;

use pocketmine\entity\Entity;

use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;

final class Blood extends PluginBase {

    protected static self $instance;

    protected function onLoad() : void{
        self::$instance = $this;
    }

    protected function onEnable() : void{
        $this->saveDefaultConfig();
        $this->validateConfig();

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

    public static function getInstance() : self{
        return self::$instance;
    }

    private function validateConfig() : void{
        foreach (["blood-particles", "lightning-effect"] as $key) {
            if (!is_bool($this->getConfig()->get($key))) {
                $this->getConfig()->set($key, true);
            }
        }
        $this->saveConfig();
    }

    public function isBloodParticlesEnabled() : bool{
        return (bool) $this->getConfig()->get("blood-particles", true);
    }

    public function isLightningEffectEnabled() : bool{
        return (bool) $this->getConfig()->get("lightning-effect", true);
    }

    public function sendLightning(Player $player) : void{
        if (!$this->isLightningEffectEnabled()) {
            return;
        }

        $pos = $player->getPosition();
        $packet = new AddActorPacket();
        $packet->actorUniqueId = Entity::nextRuntimeId();
        $packet->actorRuntimeId = 1;
        $packet->position = $player->getPosition()->asVector3();
        $packet->type = EntityIds::LIGHTNING_BOLT;
        $packet->yaw = $player->getLocation()->getYaw();
        $packet->syncedProperties = new PropertySyncData([], []);
        $sound = PlaySoundPacket::create("ambient.weather.thunder", $pos->getX(), $pos->getY(), $pos->getZ(), 100, 1);
        NetworkBroadcastUtils::broadcastPackets($player->getWorld()->getPlayers(), [$packet, $sound]);

        $block = $player->getWorld()->getBlock($player->getPosition()->floor()->down());
        $particle = new BlockBreakParticle($block);
        $player->getWorld()->addParticle($pos, $particle, $player->getWorld()->getPlayers());
    }
}