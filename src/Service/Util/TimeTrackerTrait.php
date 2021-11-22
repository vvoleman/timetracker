<?php

namespace App\Service\Util;

trait TimeTrackerTrait {
    private $start;
    private $stop;

    public function start() {
        $this->start = microtime(true);
    }

    public function stop(): float{
        $this->stop = microtime(true);
        return $this->stop - $this->start;
    }
}