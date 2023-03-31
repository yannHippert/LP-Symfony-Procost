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

    public function listLatest(): Response
    {
        $latestWorktimes = $this->worktimeRepository->getLatest(6);

        return $this->render('main/components/_worktimes_list.html.twig', [
            'worktimes' => $latestWorktimes,
            'title' => 'Temps de production'
        ]);
    }

    public function listProjectWorktimes(string $route, int $projectId, int $page = 1): Response
    {
        $totalWorktimes = $this->worktimeRepository->countOfProject($projectId);
        $numberOfPages = max(1, ceil($totalWorktimes / Worktime::PAGE_SIZE));
        if($page < 1 || $numberOfPages < $page) {
            return $this->render('components/_invalid_pagination.html.twig', [
                'title' => 'Historique des temps de production',
                'id' => $projectId,
                'route' => $route,
                'last_page' => $numberOfPages
            ]);
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
        $totalWorktimes = $this->worktimeRepository->countOfEmployee($employeeId);
        $numberOfPages = max(1, ceil($totalWorktimes / Worktime::PAGE_SIZE));
        if($page < 1 || $numberOfPages < $page) {
            return $this->render('components/_invalid_pagination.html.twig', [
                'title' => 'Historique des temps de production',
                'id' => $employeeId,
                'route' => $route,
                'last_page' => $numberOfPages
            ]);
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
