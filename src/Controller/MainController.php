<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EmployeeRepository;
use App\Repository\ProjectRepository;
use App\Repository\WorkTimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class MainController extends AbstractController
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private EmployeeRepository $employeeRepository,
        private WorkTimeRepository $workTimeRepository
    ) {}

    #[Route('/', name: 'main_dashboard', methods: 'GET')]
    public function dashboard(): Response
    {
        $openProjects = $this->projectRepository->getAllOpen();
        $openProjectsCount = count($openProjects);
        $deliveredProjects = $this->projectRepository->getAllDelivered();
        $deliveredProjectsCount = count($deliveredProjects);
        $employeeCount = $this->employeeRepository->count([]);
        $deliveryRate = round($deliveredProjectsCount / ($openProjectsCount + $deliveredProjectsCount) * 100);
        $latestProjects = $this->projectRepository->getLatest(6);
        $latestWorktimes = $this->workTimeRepository->getLatest(6);
        $oldestProject = $this->projectRepository->getOldest();
        // $productionDays = (new \DateTime())->diff($oldestProject->getCreatedAt())->format('%a');
        $interval = (new \DateTime())->diff($oldestProject->getCreatedAt());
        $productionDays = $interval->format('%a');
        $productionTime = floor($interval->days / 365) . " ans, " . $interval->days % 365 . " jours";

        return $this->render('main/dashboard.html.twig', [
            "delivered_projects" => $deliveredProjectsCount,
            "open_projects" => $openProjectsCount,
            "employee_count" => $employeeCount,
            "delivery_rate" => $deliveryRate,
            "latest_worktimes" => $latestWorktimes,
            "latest_projects" => $latestProjects,
            "production_days" => $productionDays,
            "production_time" => $productionTime
        ]);
    }
}
