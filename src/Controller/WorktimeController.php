<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\Worktime;
use App\EventManager\WorktimeManager;
use App\Form\WorktimeDataType;
use App\Form\Data\WorktimeData;
use App\Repository\EmployeeRepository;
use App\Repository\WorktimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WorktimeController extends AbstractController
{
    public function __construct(
        private WorktimeManager $worktimeManager,
        private WorktimeRepository $worktimeRepository
    ) {}

    public function adder(Request $request, Employee $employee): Response
    {
        $worktimeData = new WorktimeData();
        $form = $this->createForm(WorktimeDataType::class, $worktimeData);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->worktimeManager->addWorktime($worktimeData, $employee);

            $worktimeData = new WorktimeData();
        }

        return $this->render('employee/components/time_adder.html.twig', [
            'form' => $form
        ]);
    }

    public function listProjectWorktimes(string $route, int $projectId, int $page = 1): Response
    {
        $worktimes = $this->worktimeRepository->getOfProject($projectId, $page);
        $totalWorktimes = $this->worktimeRepository->countOfProject($projectId);

        return $this->render('project/components/_worktimes.html.twig', [
            'project_id' => $projectId,
            'worktimes' => $worktimes,
            'pagination' => [
                'current' => $page,
                'total' => max(1, ceil($totalWorktimes / Worktime::PAGE_SIZE))
            ],
            'route' => $route
        ]);
    }

    public function listEmployeeWorktimes(string $route, int $employeeId, int $page = 1): Response
    {
        $worktimes = $this->worktimeRepository->getOfEmployee($employeeId, $page);
        $totalWorktimes = $this->worktimeRepository->countOfEmployee($employeeId);

        return $this->render('employee/components/_worktimes.html.twig', [
            'employee_id' => $employeeId,
            'worktimes' => $worktimes,
            'pagination' => [
                'current' => $page,
                'total' => max(1, ceil($totalWorktimes / Worktime::PAGE_SIZE))
            ],
            'route' => $route
        ]);
    }

}
