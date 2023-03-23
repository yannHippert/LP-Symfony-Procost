<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\Worktime;
use App\EventManager\EmployeeManager;
use App\EventManager\WorktimeManager;
use App\Factory\Employee\EmployeeFactoryInterface;
use App\Form\Data\WorktimeData;
use App\Form\EmployeeType;
use App\Form\WorktimeDataType;
use App\Repository\EmployeeRepository;
use App\Repository\ProjectRepository;
use App\Repository\WorktimeRepository;
use Doctrine\ORM\UnexpectedResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class EmployeeController extends AbstractController
{
    public function __construct(
        private EmployeeRepository $employeeRepository,
        private ProjectRepository $projectRepository,
        private WorktimeRepository $workTimeRepository,
        private EmployeeFactoryInterface $employeeFactory,
        private EmployeeManager $employeeManager,
        private WorktimeManager $workTimeManager
    ) {}

    #[Route('/employee/{id}/{page}', name: 'employee_details', requirements: ['id' => '\d+', 'page' => '\d+'], methods: ['GET', 'POST'])]
    public function details(Request $request, int $id, int $page = 1): Response
    {
        try {
            $employee = $this->employeeRepository->getById($id);
        } catch(UnexpectedResultException) {
            throw new NotFoundHttpException();
        }
        
        $workTimeData = new WorktimeData();
        $form = $this->createForm(WorktimeDataType::class, $workTimeData);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if($workTimeData->getProject()->getDeliveredAt() != null) {
                throw new BadRequestHttpException();
            }

            $this->workTimeManager->addWorktime($workTimeData, $employee);

            return $this->redirectToRoute('employee_details', ['id' => $id, 'page' => $page]);
        }

        return $this->render('employee/details.html.twig', [
            "employee" => $employee,
            "page" => $page,
            "form" => $form,
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
                'total' => max(1, ceil($totalEmployees / Worktime::PAGE_SIZE))
            ]
        ]);
    }

    #[Route('/employee/create', name: 'employee_create', methods: ['GET', 'POST'])]
    public function create_employee(Request $request): Response
    {
        $employee = $this->employeeFactory->createEmployee();

        return $this->employee_form($request, $employee, "");
    }

    #[Route('/employee/{id}/update', name: 'employee_update', methods: ['GET', 'POST'], requirements: ['page' => '\d+'])]
    public function update_employee(Request $request, int $id): Response
    {
        try {
            $employee = $this->employeeRepository->getById($id);
        } catch(UnexpectedResultException) {
            throw new NotFoundHttpException();
        }
        
        return $this->employee_form($request, $employee, "");
    }

    private function employee_form(Request $request, Employee $employee, string $title): Response
    {
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->employeeManager->updateEmployee($employee);

            return $this->redirectToRoute('employee_details', ["id" => $employee->getId()]);
        }

        return $this->render('employee/form.html.twig', [
            "form" => $form->createView(),
            "title" => $title
        ]);
    }
}
