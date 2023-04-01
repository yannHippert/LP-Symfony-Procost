<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\Profession;
use App\EventManager\ProfessionManager;
use App\Factory\Profession\ProfessionFactoryInterface;
use App\Form\ProfessionType;
use App\Repository\EmployeeRepository;
use App\Repository\ProfessionRepository;
use Doctrine\ORM\UnexpectedResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

enum ProfessionFormType
{
    case Create;
    case Update;
}

class ProfessionController extends AbstractController
{

    public function __construct(
        private ProfessionRepository $professionRepository,
        private ProfessionFactoryInterface $professionFactory,
        private ProfessionManager $professionManager,
        private EmployeeRepository $employeeRepository,
    ) {}

    #[Route('/professions/{page}', name: 'professions_list', methods: 'GET', requirements: ['page' => '\d+'])]
    public function list_professions(int $page = 1): Response
    {
        $totalProfessions = $this->professionRepository->count([]);
        $numberOfPages = max(1, ceil($totalProfessions / Profession::PAGE_SIZE));
        if($page < 1 || $numberOfPages < $page) {
            throw new NotFoundHttpException();
        }

        $professions = $this->professionRepository->getPage($page);

        return $this->render('profession/list.html.twig', [
            'professions' => $professions,
            'pagination' => [
                'current' => $page,
                'total' => $numberOfPages
            ]
        ]);
    }

    #[Route('/profession/create', name: 'profession_create', methods: ['GET', 'POST'])]
    public function create_profession(Request $request): Response
    {
        $profession = $this->professionFactory->createProfession();

        return $this->profession_form($request, $profession, ProfessionFormType::Create);
    }

    #[Route('/profession/{id}/{page}', name: 'profession_details', requirements: ['id' => '\d+', 'page' => '\d+'], methods: ['GET', 'POST'])]
    public function details(int $id, int $page = 1): Response
    {
        try {
            $profession = $this->professionRepository->getById($id);
        } catch(UnexpectedResultException) {
            throw new NotFoundHttpException();
        }

        $totalEmployees = $this->employeeRepository->countOfProfession($id);
        $numberOfPages = max(1, ceil($totalEmployees / Employee::PAGE_SIZE));
        if($page < 1 || $numberOfPages < $page) {
            throw new NotFoundHttpException();
        }
        
        $page = min(max(1, $page), $numberOfPages);
        $employees = $this->employeeRepository->getOfProfession($id, max(1, $page));

        return $this->render('profession/details.html.twig', [
            'profession' => $profession,
            'page' => $page,
            'employees' => $employees,
            'pagination' => [
                'current' => $page,
                'total' => $numberOfPages
            ],
        ]);
    }

    #[Route('/profession/{id}/update', name: 'profession_update', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function update_profession(Request $request, int $id): Response
    {
        try {
            $profession = $this->professionRepository->getById($id);
        } catch(UnexpectedResultException) {
            throw new NotFoundHttpException();
        }

        return $this->profession_form($request, $profession, ProfessionFormType::Update);
    }

    #[Route('/profession/{id}/delete', name: 'profession_delete', methods: 'GET', requirements: ['id' => '\d+'])]
    public function delete_profession(int $id): Response
    {
        try {
            $profession = $this->professionRepository->getById($id);
        } catch(UnexpectedResultException) {
            throw new NotFoundHttpException();
        }

        if(count($profession->getEmployees()) > 0) {
            throw new AccessDeniedHttpException();
        }

        $this->professionManager->deleteProfession($profession);

        return $this->redirectToRoute('professions_list');
    }

    private function profession_form(Request $request, Profession $profession, ProfessionFormType $formType): Response
    {
        $form = $this->createForm(ProfessionType::class, $profession);
        $form->handleRequest($request);

        switch($formType) {
            case ProfessionFormType::Create:
                $action = "addProfession";
                $title = "Création d'une profession";
                break;
            case ProfessionFormType::Update:
                $action = "updateProfession";
                $title = "Edition d'une profession";
                break;
            default: 
                throw new HttpException(500, "Invalid form-type");
        }

        if($form->isSubmitted() && $form->isValid()) {
            if(!method_exists($this->professionManager, $action)) {
                throw new HttpException(500, "Method $action not found in ProfessionManager");
            }

            $this->professionManager->$action($profession);
            return $this->redirectToRoute('profession_details', ["id" => $profession->getId()]);
        }

        return $this->render('profession/form.html.twig', [
            "form" => $form->createView(),
            "title" => $title
        ]);
    }

}
