<?php

declare(strict_types=1);

namespace Ki;

use pocketmine\scheduler\Task;
use Ki\DailyAttendance;

class CheckingTask extends Task {

	private DailyAttendance $plugin;

	public function __construct(DailyAttendance $plugin){
		$this->plugin = $plugin;
	}

	public function onRun() : void {
		$this->plugin->reset();
	}
}