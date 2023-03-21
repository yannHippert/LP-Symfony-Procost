<?php

namespace App\DataFixtures;

use App\Entity\Employee;
use App\Entity\Profession;
use App\Factory\Employee\EmployeeFactoryInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class AppFixtures extends Fixture
{
    public const PROFESSION_COUNT = 6;
    public const EMPLOYEE_COUNT = 32;

    private $manager;
    private $faker;

    public function __construct(
        private EmployeeFactoryInterface $employeeFactory,
    ){}

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->faker = Factory::create('fr_FR');

        $this->loadProfessions();
        $this->loadEmployees();

        $this->manager->flush();
    }

    private function loadProfessions(): void
    {
        for($i = 0; $i < self::PROFESSION_COUNT;  $i++) {
            $profession = new Profession($this->faker->jobTitle());

            $this->manager->persist($profession);
            $this->addReference(Profession::class . $i, $profession);
        }
    }

    private function loadEmployees(): void
    {
        for($i = 0; $i < self::EMPLOYEE_COUNT;  $i++) {
            $employee = $this->employeeFactory->createEmployee();
            $employee->setProfession($this->getReference(Profession::class . mt_rand(0, self::PROFESSION_COUNT - 1)));

            $this->manager->persist($employee);
            $this->addReference(Employee::class . $i, $employee);
        }
    }
}
