<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class MainController extends AbstractController
{
    public function __construct(
    ) {}

    #[Route('/', name: 'main_dashboard', methods: 'GET')]
    public function dashboard(): Response
    {
        return $this->render('main/dashboard.html.twig', 
            []);
    }

    #[Route('/list', name: 'main_list_projects', methods: 'GET')]
    public function list_projects(): Response
    {
        return $this->render('main/list.html.twig', 
            []);
    }

    #[Route('/list', name: 'main_list_employees', methods: 'GET')]
    public function list_employees(): Response
    {
        return $this->render('main/list.html.twig', 
            []);
    }

    #[Route('/employee', name: 'employee_details', methods: 'GET')]
    public function details(): Response
    {
        return $this->render('main/details.html.twig', 
            []);
    }

    #[Route('/employee/create', name: 'employee_create', methods: 'GET')]
    public function create_employee(): Response
    {
        return $this->render('main/create_employee.html.twig', 
            []);
    }
}
