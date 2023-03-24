<?php

namespace App\DataFixtures;

use App\Entity\Employee;
use App\Entity\Profession;
use App\Entity\Worktime;
use App\Factory\Employee\EmployeeFactoryInterface;
use App\Factory\Profession\ProfessionFactoryInterface;
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
    public const PROJECT_DELIVERED_PERCENTAGE = 75;

    private $manager;
    private $faker;

    public function __construct(
        private EmployeeFactoryInterface $employeeFactory,
        private ProjectFactoryInterface $projectFactory,
        private ProfessionFactoryInterface $professionFactory,
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
        $professionNames = [
            ["Web designer"], 
            ["SEO Manager"], 
            ["Web Developer"], 
            ["UI/UX Designer"], 
            ["Backend Developer"],
            ["Scrum Master"],
            ["Project Manager"],
        ];

        for($i = 0; $i < max(self::PROFESSION_COUNT, count($professionNames));  $i++) {
            if($i > count($professionNames)) {
                $profession = $this->professionFactory->createProfession();
            } else {
                $profession = new Profession();
                $profession->setName($professionNames[$i][0]);
            }
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
            $target = mt_rand(0, $project->getPrice() + 5000);
            $sum = 0;
            while($sum <= $target) {
                $worktimeData = (new WorktimeData())
                    ->setProject($project)
                    ->setDaysSpent($this->faker->randomDigitNotZero());
                $worktime = new Worktime(
                    $worktimeData, 
                    $this->getReference(Employee::class . mt_rand(0, self::EMPLOYEE_COUNT - 1))
                );
                $worktime->setCreatedAt($this->faker->dateTimeBetween(
                    $project->getCreatedAt()->format('Y-M-d H:m:s'), 
                    'now'
                ));
                $sum += $worktime->getTotalPrice();
                $this->manager->persist($worktime);
            }

            if(random_int(0, 100) <= self::PROJECT_DELIVERED_PERCENTAGE) {
                $project->setDeliveredAt($this->faker->dateTimeBetween($project->getCreatedAt()->format('Y-M-d H:m:s'), 'now'));
            }

            $this->manager->persist($project);
            $this->addReference(Project::class . $i, $project);
        }
    }
}
