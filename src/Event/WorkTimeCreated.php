<?php 

namespace App\Event;

use App\Entity\WorkTime;

final class WorkTimeCreated
{
    public function __construct(
        private WorkTime $worktime
    ) {}

    public function getWorkTime(): WorkTime
    {
        return $this->worktime;
    }
}

