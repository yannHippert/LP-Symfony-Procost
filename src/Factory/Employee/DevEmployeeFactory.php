<?php

declare(strict_types=1);

namespace App\Factory\Employee;

use App\Entity\Employee;
use Faker\Factory;

class DevEmployeeFactory implements EmployeeFactoryInterface
{
    public function createEmployee(): Employee {
        $faker = Factory::create('fr_FR');

        $employee = new Employee();

        $employee
            ->setFirstName($faker->name())
            ->setLastName($faker->lastName())
            ->setEmail($faker->email())
            ->setDailySalary(mt_rand(10, 100) * 10)
            ->setEmploymentDate($faker->dateTimeBetween('-25 year', 'now'));

        return $employee;
    }
}