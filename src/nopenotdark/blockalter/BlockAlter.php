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
use pocketmine\item\StringToItemParser;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\SingletonTrait;

const CONFIG_VERSION = "1.0.0";

class BlockAlter extends PluginBase {
    use SingletonTrait;

    /** @var array */
    private array $replaceables = [];

    public function onEnable(): void {
        self::setInstance($this);

        $this->saveDefaultConfig();

        $config = $this->getConfig();

        if ($config->get("version") !== CONFIG_VERSION) {
            $this->getLogger()->error("Config version is not valid. Please delete the config.yml file and restart the server.");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        $blocks = $config->get("blocks");

        foreach ($blocks as $block => $data) {
            $world = $data["world"];
            $replace = $data["replace"];
            $drops = $data["drops"];
            $duration = $data["duration"];

            if (!isset($this->replaceables[$world])) {
                $this->replaceables[$world] = [];
            }

            $updatedDrops = [];
            foreach ($drops as $drop) {
                $updatedDrops[] = StringToItemParser::getInstance()->parse($drop);
            }

            $this->replaceables[$world][abs(StringToItemParser::getInstance()->parse($block)->getTypeId())] = [$replace, $updatedDrops, $duration];
            $strDrops = implode(", ", $drops);
            $this->getLogger()->debug("Registered block '$block' to be replaced with $replace, with drops [$strDrops] in $world for $duration seconds after broken.");
        }

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

    public function onDisable(): void {
        $scheduler = $this->getScheduler();

        $reflectionClass = new \ReflectionClass($scheduler);
        $tasksProperty = $reflectionClass->getProperty("tasks");
        $tasksProperty->setAccessible(true);

        $tasks = $tasksProperty->getValue($scheduler);

        /**
         * Simple method to revert all Blocks that are queued to be replaced.
         * @var TaskHandler $tHandler
         */
        foreach ($tasks as $tHandler) {
            $task = $tHandler->getTask();
            if ($task instanceof BlockReplaceTask) {
                $task->revert();
            }
        }
    }

    public function getReplaceables(): array {
        return $this->replaceables;
    }
}