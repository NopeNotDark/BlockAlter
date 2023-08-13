<?php

/**
 * `7MM"""Mq.                 `7MM              mm        db     `7MMF'
 *   MM   `MM.                  MM              MM       ;MM:      MM
 *   MM   ,M9 ,pW"Wq.   ,p6"bo  MM  ,MP.gP"Ya mmMMmm    ,V^MM.     MM
 *   MMmmdM9 6W'   `Wb 6M'  OO  MM ;Y ,M'   Yb  MM     ,M  `MM     MM
 *   MM      8M     M8 8M       MM;Mm 8M""""""  MM     AbmmmqMA    MM
 *   MM      YA.   ,A9 YM.    , MM `MbYM.    ,  MM    A'     VML   MM
 * .JMML.     `Ybmd9'   YMbmd'.JMML. YA`Mbmmd'  `Mbm.AMA.   .AMMA.JMML.
 *
 * This file was generated using PocketAI, Branch V7.11.3+dev
 *
 * PocketAI is private software: You can redistribute the files under
 * the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this file. If not, see <http://www.gnu.org/licenses/>.
 *
 * @ai-profile NopeNotDark
 * @copyright 2023
 * @authors NopeNotDark, SantanasWrld
 * @link https://thedarkproject.net/pocketai
 */

declare(strict_types=1);

namespace nopenotdark\blockalter;

use nopenotdark\blockalter\task\BlockReplaceTask;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\item\StringToItemParser;
use pocketmine\world\particle\BlockBreakParticle;

class EventListener implements Listener {
    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $world = $player->getWorld();
        $block = $event->getBlock();
        $inventory = $player->getInventory();

        $replaceables = BlockAlter::getInstance()->getReplaceables();

        if (!isset($replaceables[$world->getFolderName()])) {
            return;
        }

        if (!isset($replaceables[$world->getFolderName()][$block->getTypeId()])) {
            return;
        }

        $replaceData = $replaceables[$world->getFolderName()][$block->getTypeId()];
        $replace = $replaceData[0];
        $drops = empty($replaceData[1]) ? $block->getDrops($inventory->getItemInHand()) : $replaceData[1];
        $duration = $replaceData[2];

        $world->setBlock($block->getPosition(), StringToItemParser::getInstance()->parse($replace)->getBlock());
        $world->addParticle($block->getPosition(), new BlockBreakParticle($block));
        $player->getXpManager()->addXp($block->getXpDropForTool($inventory->getItemInHand()));

        $instantInventory = BlockAlter::getInstance()->getConfig()->get("instant-inventory");
        if(!empty($drops)) {
            if ($instantInventory && $inventory->canAddItem(...$drops)) {
                $inventory->addItem(...$drops);
            } else {
                $world->dropItem($block->getPosition()->add(0, 1, 0), ...$drops);
            }
        }

        $delayedTask = new BlockReplaceTask($world, $block->getPosition(), $block);
        BlockAlter::getInstance()->getScheduler()->scheduleDelayedTask($delayedTask, $duration * 20);
        $event->cancel();
    }
}