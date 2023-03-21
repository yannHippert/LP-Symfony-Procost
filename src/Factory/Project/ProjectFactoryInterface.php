<?php

declare(strict_types=1);

namespace App\Factory\Project;

use App\Entity\Project;

interface ProjectFactoryInterface {
    public function createProject(): Project;
}