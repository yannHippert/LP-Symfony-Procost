<?php 

namespace App\Event;

use App\Entity\Employee;

final class EmployeeCreated
{
    public function __construct(
        private Employee $employee
    ) {}

    public function getEmployee(): Employee
    {
        return $this->employee;
    }
}

