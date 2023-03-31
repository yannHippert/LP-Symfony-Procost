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
use App\Repository\ProfessionRepository;
use App\Repository\ProjectRepository;
use App\Repository\WorktimeRepository;
use Doctrine\ORM\UnexpectedResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

enum EmployeeFromType
{
    case Create;
    case Update;  
}

class EmployeeController extends AbstractController
{
    public function __construct(
        private EmployeeRepository $employeeRepository,
        private ProjectRepository $projectRepository,
        private WorktimeRepository $workTimeRepository,
        private EmployeeFactoryInterface $employeeFactory,
        private EmployeeManager $employeeManager,
        private WorktimeManager $workTimeManager,
        private ProfessionRepository $professionRepository,
        private RouterInterface $router,
    ) {}

    #[Route('/employees/{page}', name: 'employees_list', methods: 'GET', requirements: ['page' => '\d+'])]
    public function list_employees(int $page = 1): Response
    {
        $totalEmployees = $this->employeeRepository->count([]);
        $numberOfPages = max(1, ceil($totalEmployees / Employee::PAGE_SIZE));
        if($page < 1 || $numberOfPages < $page) {
            throw new NotFoundHttpException();
        }

        $employees = $this->employeeRepository->getPage($page);

        return $this->render('employee/list.html.twig', [
            'employees' => $employees,
            'pagination' => [
                'current' => $page,
                'total' => $numberOfPages
            ]
        ]);
    }

    #[Route('/employee/create', name: 'employee_create', methods: ['GET', 'POST'])]
    public function create_employee(Request $request): Response
    {
        $employee = $this->employeeFactory->createEmployee();

        return $this->employee_form($request, $employee, EmployeeFromType::Create);
    }

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
            'employee' => $employee,
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/employee/{id}/update', name: 'employee_update', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function update_employee(Request $request, int $id): Response
    {
        try {
            $employee = $this->employeeRepository->getById($id);
        } catch(UnexpectedResultException) {
            throw new NotFoundHttpException();
        }
        
        return $this->employee_form($request, $employee, EmployeeFromType::Update);
    }

    private function employee_form(Request $request, Employee $employee, EmployeeFromType $formType): Response
    {
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        switch($formType) {
            case EmployeeFromType::Create:
                $action = "addEmployee";
                $title = "Création d'un employé";
                break;
            case EmployeeFromType::Update:
                $action = "updateEmployee";
                $title = "Edition d'un employé";
                break;
        }

        if($form->isSubmitted() && $form->isValid()) {
            if(!method_exists($this->employeeManager, $action)) {
                throw new HttpException(500, "Method $action not found in ProfessionManager");
            }

            $this->employeeManager->$action($employee);
            return $this->redirectToRoute('employee_details', ['id' => $employee->getId()]);
        }

        return $this->render('employee/form.html.twig', [
            'form' => $form->createView(),
            'title' => $title
        ]);
    }

    public function listOfProfession(string $route, int $professionId, int $page = 1): Response
    {
        $totalEmployees = $this->employeeRepository->countOfProfession($professionId);
        $numberOfPages = max(1, ceil($totalEmployees / Employee::PAGE_SIZE));
        if($page < 1 || $numberOfPages < $page) {
            return $this->render('components/_invalid_pagination.html.twig', [
                'title' => 'Employés',
                'id' => $professionId,
                'route' => $route,
                'last_page' => $numberOfPages
            ]);
        }
        
        $page = min(max(1, $page), $numberOfPages);
        $employees = $this->employeeRepository->getOfProfession($professionId, max(1, $page));

        return $this->render('profession/components/_employees_table.html.twig', [
            'profession_id' => $professionId,
            'employees' => $employees,
            'pagination' => [
                'current' => $page,
                'total' => $numberOfPages
            ],
            'route' => $route,
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
