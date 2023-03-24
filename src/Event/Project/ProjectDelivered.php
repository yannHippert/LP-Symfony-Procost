<?php 

namespace App\Event\Project;

use App\Entity\Project;

final class ProjectDelivered
{
    public function __construct(
        private Project $project
    ) {}

    public function getProject(): Project
    {
        return $this->project;
    }
}
