<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\ORM\UnexpectedResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    public function __construct(
        private ProjectRepository $projectRepository
    ) {}

    #[Route('/project/{id}', name: 'project_details', requirements: ['id' => '\d+'], methods: 'GET')]
    public function index(int $id): Response
    {
        try {
            $project = $this->projectRepository->getById($id);
        } catch(UnexpectedResultException) {
            throw new NotFoundHttpException();
        }

        return $this->render('project/details.html.twig', [
            "project" => $project
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

    #[Route('/project/create', name: 'project_create', methods: 'GET')]
    public function create_project(): Response
    {
        return $this->render('project/create.html.twig', 
            []);
    }

    
    #[Route('/project/{id}/update', name: 'project_update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update_project(): Response
    {
        return $this->render('project/update.html.twig', 
            []);
    }
}
