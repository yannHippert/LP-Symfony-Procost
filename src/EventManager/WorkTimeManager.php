<?php 

namespace App\EventManager;

use App\Entity\Employee;
use App\Entity\WorkTime;
use App\Event\EmployeeCreated;
use App\Event\WorkTimeCreated;
use App\Form\Data\WorkTimeData;
use App\Repository\WorkTimeRepository;
use Psr\EventDispatcher\EventDispatcherInterface;

final class WorkTimeManager
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private WorkTimeRepository $workTimeRepository,
    ) {}

    public function addWorkTime(WorkTimeData $worktimeData, Employee $employee): void
    {
        $worktime = new WorkTime($worktimeData, $employee);

        $this->workTimeRepository->save($worktime, true);

        $this->eventDispatcher->dispatch(new WorkTimeCreated($worktime));
    }
}
