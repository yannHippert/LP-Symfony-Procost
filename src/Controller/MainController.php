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
        $productionTime = $this->worktimeRepository->getGlobalProductionTime();
        $profitableProjectsCount = $this->projectRepository->countProfitable();
        $profitableRate = round($profitableProjectsCount / $totalProjectsCount * 100);

        return $this->render('main/dashboard.html.twig', [
            "total_projects" => $totalProjectsCount,
            "delivered_projects" => $deliveredProjectsCount,
            "open_projects" => $openProjectsCount,
            "profitable_rate" => $profitableRate,
            "employee_count" => $employeeCount,
            "delivery_rate" => $deliveryRate,
            "production_time" => $productionTime,
        ]);
    }

    public function latestProjectsTable(): Response
    {
        $latestProjects = $this->projectRepository->getLatest(6);

        return $this->render('main/components/_projects_table.html.twig', [
            'projects' => $latestProjects,
            'title' => "Les derniers projets"
        ]);
    }

    public function latestWorktimesList(): Response
    {
        $latestWorktimes = $this->worktimeRepository->getLatest(6);

        return $this->render('main/components/_worktimes_list.html.twig', [
            'worktimes' => $latestWorktimes,
            'title' => "Temps de production"
        ]);
    }

    public function bestEmployeeCard(): Response
    {
        return $this->employeeCard();
    }

    public function employeeCard(?int $employeeId = null): Response
    {
        $employee = $employeeId ? $this->employeeRepository->getById($employeeId) : $this->employeeRepository->getBest();

        return $this->render('main/components/_employee_card.html.twig', [
            'employee' => $employee,
            'title' => $employeeId ? "Employé" : "Top employé"
        ]);
    }

}
