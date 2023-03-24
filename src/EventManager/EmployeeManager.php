<?php 

namespace App\EventManager;

use App\Entity\Employee;
use App\Event\Employee\EmployeeCreated;
use App\Event\Employee\EmployeeUpdated;
use App\Repository\EmployeeRepository;
use Psr\EventDispatcher\EventDispatcherInterface;

final class EmployeeManager
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EmployeeRepository $employeeRepository,
    ) {}

    public function addEmployee(Employee $employee): void
    {
        $this->employeeRepository->save($employee, true);

        $this->eventDispatcher->dispatch(new EmployeeCreated($employee));
    }

    public function updateEmployee(Employee $employee): void
    {
        $this->employeeRepository->save($employee, true);

        $this->eventDispatcher->dispatch(new EmployeeUpdated($employee));
    }
}
