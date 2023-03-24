<?php

namespace App\Controller;

use App\Entity\Worktime;
use App\EventManager\WorktimeManager;
use App\Repository\WorktimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class WorktimeController extends AbstractController
{
    public function __construct(
        private WorktimeManager $worktimeManager,
        private WorktimeRepository $worktimeRepository
    ) {}

    public function listProjectWorktimes(string $route, int $projectId, int $page = 1): Response
    {
        if($page < 1) {
             return $this->redirectToRoute($route, ['id' => $projectId]);
        }
        
        $totalWorktimes = $this->worktimeRepository->countOfProject($projectId);
        $numberOfPages = max(1, ceil($totalWorktimes / Worktime::PAGE_SIZE));
        if($page > $numberOfPages) {
            return $this->redirectToRoute($route, ['id' => $projectId, 'page' => $numberOfPages]);
        }
        
        $worktimes = $this->worktimeRepository->getOfProject($projectId, $page);

        return $this->render('project/components/_worktimes.html.twig', [
            'project_id' => $projectId,
            'worktimes' => $worktimes,
            'pagination' => [
                'current' => $page,
                'total' =>  $numberOfPages
            ],
            'route' => $route
        ]);
    }

    public function listEmployeeWorktimes(string $route, int $employeeId, int $page = 1): Response
    {
        if($page < 1) {
            return $this->redirectToRoute($route, ['id' => $employeeId]);
        }

        $totalWorktimes = $this->worktimeRepository->countOfEmployee($employeeId);
        $numberOfPages = max(1, ceil($totalWorktimes / Worktime::PAGE_SIZE));
        if($page > $numberOfPages) {
            return $this->redirectToRoute($route, ['id' => $employeeId, 'page' => $numberOfPages]);
        }

        $worktimes = $this->worktimeRepository->getOfEmployee($employeeId, $page);

        return $this->render('employee/components/_worktimes.html.twig', [
            'employee_id' => $employeeId,
            'worktimes' => $worktimes,
            'pagination' => [
                'current' => $page,
                'total' => $numberOfPages
            ],
            'route' => $route
        ]);
    }

}
