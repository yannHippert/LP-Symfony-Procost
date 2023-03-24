<?php

declare(strict_types=1);

namespace App\Factory\Profession;

use App\Entity\Profession;

class DefaultProfessionFactory implements ProfessionFactoryInterface
{
    public function createProfession(): Profession 
    {
        $profession = new Profession();

        return $profession;
    }
}