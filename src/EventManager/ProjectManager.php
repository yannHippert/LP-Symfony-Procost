<?php 

namespace App\EventManager;

use App\Entity\Project;
use App\Event\Project\ProjectCreated;
use App\Event\Project\ProjectDelivered;
use App\Event\Project\ProjectUpdated;
use App\Repository\ProjectRepository;
use Psr\EventDispatcher\EventDispatcherInterface;

final class ProjectManager
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private ProjectRepository $projectRepository,
    ) {}

    public function addProject(Project $project): void
    {
        $this->projectRepository->save($project, true);

        $this->eventDispatcher->dispatch(new ProjectCreated($project));
    }

    public function updateProject(Project $project): void
    {
        $this->projectRepository->save($project, true);

        $this->eventDispatcher->dispatch(new ProjectUpdated($project));
    }

    public function deliverProject(Project $project): void
    {
        $this->projectRepository->save($project, true);

        $this->eventDispatcher->dispatch(new ProjectDelivered($project));
    }
}
