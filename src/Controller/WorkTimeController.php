<?php

namespace App\Controller;

use App\Entity\Employee;
use App\EventManager\WorkTimeManager;
use App\Form\WorkTimeDataType;
use App\Form\Data\WorkTimeData;
use App\Repository\EmployeeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkTimeController extends AbstractController
{
    public function __construct(
        private WorkTimeManager $workTimeManager,
    ) {}

    public function adder(Request $request, Employee $employee): Response
    {
        $workTimeData = new WorkTimeData();
        $form = $this->createForm(WorkTimeDataType::class, $workTimeData);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->workTimeManager->addWorkTime($workTimeData, $employee);

            $workTimeData = new WorkTimeData();
        }

        return $this->render('employee/components/time_adder.html.twig', [
            'form' => $form
        ]);
    }
}
