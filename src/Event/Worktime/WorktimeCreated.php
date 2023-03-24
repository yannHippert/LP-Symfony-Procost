<?php 

namespace App\Event\Worktime;

use App\Entity\Worktime;

final class WorktimeCreated
{
    public function __construct(
        private Worktime $worktime
    ) {}

    public function getWorktime(): Worktime
    {
        return $this->worktime;
    }
}

