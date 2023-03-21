<?php

declare(strict_types=1);

namespace App\Factory\Project;

use App\Entity\Project;
use Faker\Factory;

class DevProjectFactory implements ProjectFactoryInterface
{
    public function createProject(): Project {
        $faker = Factory::create('fr_FR');

        $project = new Project();

        $project
            ->setName($faker->sentence(3))
            ->setDescription($faker->paragraph())
            ->setPrice(mt_rand(100, 550) * 100)
            ->setCreatedAt($faker->dateTimeBetween('-5 year', 'now'));

        return $project;
    }
}