<?php

declare(strict_types=1);

namespace App\Factory\Employee;

use App\Entity\Employee;

class DefaultEmployeeFactory implements EmployeeFactoryInterface
{
    public function createEmployee(): Employee 
    {
        $employee = new Employee();

        return $employee;
    }
}