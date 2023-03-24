<?php

declare(strict_types=1);

namespace App\Factory\Profession;

use App\Entity\Profession;
use Faker\Factory;

class DevProfessionFactory implements ProfessionFactoryInterface
{
    public function createProfession(): Profession {
        $faker = Factory::create('fr_FR');

        $profession = new Profession();

        $profession
            ->setName($faker->jobTitle());

        return $profession;
    }
}