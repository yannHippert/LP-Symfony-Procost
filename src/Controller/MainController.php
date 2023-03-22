<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EmployeeRepository;
use App\Repository\ProjectRepository;
use App\Repository\WorktimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class MainController extends AbstractController
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private EmployeeRepository $employeeRepository,
        private WorktimeRepository $worktimeRepository
    ) {}

    #[Route('/', name: 'main_dashboard', methods: 'GET')]
    public function dashboard(): Response
    {
        $openProjects = $this->projectRepository->getAllOpen();
        $openProjectsCount = count($openProjects);
        $deliveredProjects = $this->projectRepository->getAllDelivered();
        $deliveredProjectsCount = count($deliveredProjects);
        $totalProjectsCount = $openProjectsCount + $deliveredProjectsCount;
        $employeeCount = $this->employeeRepository->count([]);
        $deliveryRate = round($deliveredProjectsCount / $totalProjectsCount * 100);
        $latestProjects = $this->projectRepository->getLatest(6);
        $latestWorktimes = $this->worktimeRepository->getLatest(6);
        $productionTime = $this->worktimeRepository->getGlobalProductionTime();
        $bestEmployee = $this->employeeRepository->getBest();
        $profitableProjectsCount = $this->projectRepository->countProfitable();
        $profitableRate = round($profitableProjectsCount / $totalProjectsCount * 100);

        return $this->render('main/dashboard.html.twig', [
            "total_projects" => $totalProjectsCount,
            "delivered_projects" => $deliveredProjectsCount,
            "open_projects" => $openProjectsCount,
            "profitable_rate" => $profitableRate,
            "employee_count" => $employeeCount,
            "delivery_rate" => $deliveryRate,
            "latest_worktimes" => $latestWorktimes,
            "latest_projects" => $latestProjects,
            "production_time" => $productionTime,
            "best_employee" => $bestEmployee,
        ]);
    }
}
