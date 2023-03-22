<?php

declare(strict_types=1);

namespace App\Factory\Project;

use App\Entity\Project;

class DefaultProjectFactory implements ProjectFactoryInterface
{
    public function createProject(): Project 
    {
        $project = new Project();

        $project->setCreatedAt(new \DateTime());

        return $project;
    }
}