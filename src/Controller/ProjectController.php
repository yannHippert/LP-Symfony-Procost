<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Worktime;
use App\EventManager\ProjectManager;
use App\Factory\Project\ProjectFactoryInterface;
use App\Form\ProjectType;
use App\Repository\EmployeeRepository;
use App\Repository\ProjectRepository;
use App\Repository\WorktimeRepository;
use Doctrine\ORM\UnexpectedResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

enum ProjectFormType
{
    case Create;
    case Update;  
}

class ProjectController extends AbstractController
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private EmployeeRepository $employeeRepository,
        private WorktimeRepository $worktimeRepository,
        private ProjectFactoryInterface $projectFactory,
        private ProjectManager $projectManager
    ) {}

    #[Route('/projects/{page}', name: 'projects_list', requirements: ['page' => '\d+'], methods: 'GET')]
    public function list_projects(int $page = 1): Response
    {
        $totalProjects = $this->projectRepository->count([]);
        $numberOfPages = max(1, ceil($totalProjects / Project::PAGE_SIZE));
        if($page < 1 || $numberOfPages < $page) {
            throw new NotFoundHttpException();
        }

        $projects = $this->projectRepository->getPage($page);

        return $this->render('project/list.html.twig', [
            "projects" => $projects,
            'pagination' => [
                'current' => $page,
                'total' => $numberOfPages
            ]
        ]);
    }

    #[Route('/project/create', name: 'project_create', methods: ['GET', 'POST'])]
    public function create_project(Request $request): Response
    {
        $project = $this->projectFactory->createProject();

        return $this->project_form($request, $project, ProjectFormType::Create);
    }

    #[Route('/project/{id}/{page}', name: 'project_details', requirements: ['id' => '\d+', 'page' => '\d+'], methods: 'GET')]
    public function details(int $id, int $page = 1): Response
    {
        try {
            $project = $this->projectRepository->getById($id);
        } catch(UnexpectedResultException) {
            throw new NotFoundHttpException();
        }

        $totalWorktimes = $this->worktimeRepository->countOfProject($id);
        $numberOfPages = max(1, ceil($totalWorktimes / Worktime::PAGE_SIZE));
        if($page < 1 || $numberOfPages < $page) {
            throw new NotFoundHttpException();
        }
            
        $worktimes = $this->worktimeRepository->getOfProject($id, $page);
    
        $employeeCount = $this->employeeRepository->countOfProject($id);

        return $this->render('project/details.html.twig', [
            "project" => $project,
            "employeeCount" => $employeeCount,
            'worktimes' => $worktimes,
            'pagination' => [
                'current' => $page,
                'total' =>  $numberOfPages
            ],
        ]);
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

        return $this->project_form($request, $project, ProjectFormType::Update);
    }

    #[Route('/project/{id}/deliver', name: 'project_deliver', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deliver_project(int $id): Response
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

    private function project_form(Request $request, Project $project, ProjectFormType $formType): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        switch($formType) {
            case ProjectFormType::Create:
                $action = "addProject";
                $title = "Création d'un projet";
                break;
            case ProjectFormType::Update:
                $action = "updateProject";
                $title = "Edition d'un projet";
                break;
            default: 
                throw new HttpException(500, "Invalid form-type");
        }

        if($form->isSubmitted() && $form->isValid()) {
            if(!method_exists($this->projectManager, $action)) {
                throw new HttpException(500, "Method $action not found in ProjectManager");
            }

            $this->projectManager->$action($project);

            return $this->redirectToRoute('project_details', ["id" => $project->getId()]);
        }

        return $this->render('project/form.html.twig', [
            "form" => $form->createView(),
            "title" => $title
        ]);
    }

    public function listLatest(): Response
    {
        $latestProjects = $this->projectRepository->getLatest(6);

        return $this->render('main/components/_projects_table.html.twig', [
            'projects' => $latestProjects,
            'title' => 'Les derniers projets'
        ]);
    }

}
