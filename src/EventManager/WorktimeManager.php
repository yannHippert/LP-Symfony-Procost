<?php 

namespace App\EventManager;

use App\Entity\Employee;
use App\Entity\Worktime;
use App\Event\Worktime\WorktimeCreated;
use App\Form\Data\WorktimeData;
use App\Repository\WorktimeRepository;
use Psr\EventDispatcher\EventDispatcherInterface;

final class WorktimeManager
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private WorktimeRepository $workTimeRepository,
    ) {}

    public function addWorktime(WorktimeData $worktimeData, Employee $employee): void
    {
        $worktime = new Worktime($worktimeData, $employee);

        $this->workTimeRepository->save($worktime, true);

        $this->eventDispatcher->dispatch(new WorktimeCreated($worktime));
    }
}
