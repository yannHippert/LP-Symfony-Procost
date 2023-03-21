<?php

declare(strict_types=1);

namespace App\Factory\Employee;

use App\Entity\Employee;

interface EmployeeFactoryInterface {
    public function createEmployee(): Employee;
}