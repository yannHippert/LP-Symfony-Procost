<?php

namespace App\Controller;

use App\Entity\WorkTime;
use App\EventManager\EmployeeManager;
use App\Factory\Employee\EmployeeFactoryInterface;
use App\Form\EmployeeType;
use App\Repository\EmployeeRepository;
use App\Repository\ProjectRepository;
use App\Repository\WorkTimeRepository;
use Doctrine\ORM\UnexpectedResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class EmployeeController extends AbstractController
{
    public function __construct(
        private EmployeeRepository $employeeRepository,
        private ProjectRepository $projectRepository,
        private WorkTimeRepository $workTimeRepository,
        private EmployeeFactoryInterface $employeeFactory,
        private EmployeeManager $employeeManager,
    ) {}

    #[Route('/employee/{id}/{page}', name: 'employee_details', requirements: ['id' => '\d+', 'page' => '\d+'], methods: 'GET')]
    public function details(int $id, int $page = 1): Response
    {
        try {
            $employee = $this->employeeRepository->getById($id);
        } catch(UnexpectedResultException) {
            throw new NotFoundHttpException();
        }

        $openProjects = $this->projectRepository->findOpen();
        $totalWorkTimes = $this->workTimeRepository->countOfEmployee($id);
        $workTimes = $this->workTimeRepository->findByEmployeeId($id, $page);

        return $this->render('employee/details.html.twig', [
            "employee" => $employee,
            "open_projects" => $openProjects,
            "work_times" => $workTimes,
            'pagination' => [
                'current' => $page,
                'total' => max(1, ceil($totalWorkTimes / WorkTime::PAGE_SIZE))
            ]
        ]);
    }

    #[Route('/employees/{page}', name: 'employees_list', methods: 'GET', requirements: ['page' => '\d+'])]
    public function list_employees(int $page = 1): Response
    {
        $employees = $this->employeeRepository->getPage($page);
        $totalEmployees = $this->employeeRepository->count([]);

        return $this->render('employee/list.html.twig', [
            "employees" => $employees,
            'pagination' => [
                'current' => $page,
                'total' => max(1, ceil($totalEmployees / WorkTime::PAGE_SIZE))
            ]
        ]);
    }

    #[Route('/employee/create', name: 'employee_create', methods: ['GET', 'POST'])]
    public function create_employee(Request $request): Response
    {
        $employee = $this->employeeFactory->createEmployee();
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->employeeManager->addEmployee($employee);

            return $this->redirectToRoute('employee_details', ["id" => $employee->getId()]);
        }

        return $this->render('employee/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/employee/{id}/update', name: 'employee_update', methods: ['GET', 'POST'], requirements: ['page' => '\d+'])]
    public function update_employee(Request $request, int $id): Response
    {
        try {
            $employee = $this->employeeRepository->getById($id);
        } catch(UnexpectedResultException) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->employeeManager->updateEmployee($employee);

            return $this->redirectToRoute('employee_details', ["id" => $employee->getId()]);
        }

        return $this->render('employee/update.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
