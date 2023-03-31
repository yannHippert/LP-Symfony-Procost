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
        $totalProjectsCount = $this->projectRepository->count([]);
        $openProjectsCount = $this->projectRepository->countOpen();
        $deliveredProjectsCount = $totalProjectsCount - $openProjectsCount;
        $employeeCount = $this->employeeRepository->count([]);
        $deliveryRate = round($deliveredProjectsCount / $totalProjectsCount * 100);
        $productionTime = $this->worktimeRepository->getGlobalProductionTime();
        $profitableProjectsCount = $this->projectRepository->countProfitable();
        $profitableRate = round($profitableProjectsCount / $totalProjectsCount * 100);

        return $this->render('main/dashboard.html.twig', [
            "total_projects" => $totalProjectsCount,
            "delivered_projects" => $deliveredProjectsCount,
            "open_projects" => $openProjectsCount,
            "profitable_rate" => $profitableRate,
            "delivery_rate" => $deliveryRate,
            "employee_count" => $employeeCount,
            "production_time" => $productionTime,
        ]);
    }

}
