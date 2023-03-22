<?php

namespace App\DataFixtures;

use App\Entity\Employee;
use App\Entity\Profession;
use App\Entity\Worktime;
use App\Factory\Employee\EmployeeFactoryInterface;
use App\Factory\Project\ProjectFactoryInterface;
use App\Form\Data\WorktimeData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class AppFixtures extends Fixture
{
    public const PROFESSION_COUNT = 6;
    public const EMPLOYEE_COUNT = 32;
    public const PROJECT_COUNT = 72;
    public const DELIVERED_PERCENTAGE = 75;

    private $manager;
    private $faker;

    public function __construct(
        private EmployeeFactoryInterface $employeeFactory,
        private ProjectFactoryInterface $projectFactory,
    ){}

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->faker = Factory::create('fr_FR');

        $this->loadProfessions();
        $this->loadEmployees();
        $this->loadProjects();

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

    private function loadProjects(): void
    {
        for($i = 0; $i < self::PROJECT_COUNT;  $i++) {
            $project = $this->projectFactory->createProject();
            $target = mt_rand(0, $project->getPrice() + 10000);
            $sum = 0;
            $hightestCreatedAt = null;
            while($sum <= $target) {
                $lowerDate = $project->getCreatedAt()->format("Y-M-d H:m:s");
                $upperDate = min(intval($project->getCreatedAt()->format("Y") + 2), (new \DateTime())->format("Y")) . $project->getCreatedAt()->format("-M-d H:m:s");
                $worktimeData = new WorktimeData();
                $worktimeData->setProject($project)
                    ->setDaysSpent($this->faker->randomDigitNotZero());
                $worktime = new Worktime($worktimeData, $this->getReference(Employee::class . mt_rand(0, self::EMPLOYEE_COUNT - 1)));
                $worktime->setCreatedAt($this->faker->dateTimeBetween($lowerDate, $upperDate));
                $sum += $worktime->getTotalPrice();
                $hightestCreatedAt = $hightestCreatedAt != null ?max($hightestCreatedAt, $worktime->getCreatedAt()) : $worktime->getCreatedAt();
                $this->manager->persist($worktime);
            }
            if(random_int(0, 100) <= self::DELIVERED_PERCENTAGE) {
                $project->setDeliveredAt((new \DateTime())->setTimestamp($hightestCreatedAt->getTimestamp()));
            }

            $this->manager->persist($project);
            $this->addReference(Project::class . $i, $project);
        }
    }
}
