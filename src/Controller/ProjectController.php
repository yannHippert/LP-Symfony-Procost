<?php

namespace App\Controller;

use App\Entity\Project;
use App\EventManager\ProjectManager;
use App\Factory\Project\ProjectFactoryInterface;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use App\Repository\WorktimeRepository;
use Doctrine\ORM\UnexpectedResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private WorktimeRepository $workTimeRepository,
        private ProjectFactoryInterface $projectFactory,
        private ProjectManager $projectManager
    ) {}

    #[Route('/project/{id}/{page}', name: 'project_details', requirements: ['id' => '\d+', 'page' => '\d+'], methods: 'GET')]
    public function details(int $id, int $page = 1): Response
    {
        try {
            $project = $this->projectRepository->getById($id);
        } catch(UnexpectedResultException) {
            throw new NotFoundHttpException();
        }

        $employeeCount = $this->projectRepository->countEmployeesOfProject($id);

        return $this->render('project/details.html.twig', [
            "project" => $project,
            "page" => $page,
            "employeeCount" => $employeeCount,
        ]);
    }

    #[Route('/projects/{page}', name: 'projects_list', requirements: ['page' => '\d+'], methods: 'GET')]
    public function list_projects(int $page = 1): Response
    {
        $projects = $this->projectRepository->getPage($page);
        $totalProjects = $this->projectRepository->count([]);

        return $this->render('project/list.html.twig', [
            "projects" => $projects,
            'pagination' => [
                'current' => $page,
                'total' => max(1, ceil($totalProjects / Project::PAGE_SIZE))
            ]
        ]);
    }

    #[Route('/project/create', name: 'project_create', methods: ['GET', 'POST'])]
    public function create_project(Request $request): Response
    {
        $project = $this->projectFactory->createProject();

        return $this->project_form($request, $project, "Création d'un project");
    }

    
    #[Route('/project/{id}/update', name: 'project_update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update_project(Request $request, int $id): Response
    {
        try {
            $project = $this->projectRepository->getById($id);
        } catch(UnexpectedResultException) {
            throw new NotFoundHttpException();
        }

        if($project->getDeliveredAt() != null) {
            throw new AccessDeniedHttpException();
        }

        return $this->project_form($request, $project, "Edition d'un project");
    }

    #[Route('/project/{id}/deliver', name: 'project_deliver', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deliver_project(Request $request, int $id): Response
    {
        try {
            $project = $this->projectRepository->getById($id);
        } catch(UnexpectedResultException) {
            throw new NotFoundHttpException();
        }

        if($project->getDeliveredAt() != null) {
            throw new AccessDeniedHttpException();
        }

        $project->setDeliveredAt(new \DateTime());
        $this->projectManager->deliverProject($project);

        return $this->redirectToRoute('project_details', ["id" => $project->getId()]);
    }

    private function project_form(Request $request, Project $project, string $title): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->projectManager->addProject($project);

            return $this->redirectToRoute('project_details', ["id" => $project->getId()]);
        }

        return $this->render('project/form.html.twig', [
            "form" => $form->createView(),
            "title" => $title
        ]);
    }

}
