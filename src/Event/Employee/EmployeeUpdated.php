<?php 

namespace App\Event\Employee;

use App\Entity\Employee;

final class EmployeeUpdated
{
    public function __construct(
        private Employee $employee
    ) {}

    public function getEmployee(): Employee
    {
        return $this->employee;
    }
}
