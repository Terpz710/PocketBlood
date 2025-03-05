<?php

declare(strict_types=1);

namespace terpz710\pocketblood;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;

use pocketmine\player\Player;

use pocketmine\world\Position;

use pocketmine\world\particle\BlockBreakParticle;

use pocketmine\block\VanillaBlocks;

class EventListener implements Listener {

    public function onHurt(EntityDamageEvent $event) : void{
        $entity = $event->getEntity();

        if ($entity instanceof Player && PocketBlood::getInstance()->isBloodParticlesEnabled()) {
            $position = $entity->getPosition();
            $pos = new Position($position->getX(), $position->getY() + 0.5, $position->getZ(), $position->getWorld());
            $blood = new BlockBreakParticle(VanillaBlocks::REDSTONE());

            $entity->getWorld()->addParticle($pos, $blood);
        }
    }

    public function onDeath(PlayerDeathEvent $event) : void{
        if (PocketBlood::getInstance()->isLightningEffectEnabled()) {
            PocketBlood::getInstance()->sendLightning($event->getPlayer());
        }
    }
}
