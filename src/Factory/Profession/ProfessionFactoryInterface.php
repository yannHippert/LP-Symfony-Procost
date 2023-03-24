<?php

declare(strict_types=1);

namespace App\Factory\Profession;

use App\Entity\Profession;

interface ProfessionFactoryInterface {
    public function createProfession(): Profession;
}