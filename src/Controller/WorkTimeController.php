<?php

namespace App\Controller;

use App\Form\WorkTimeDataType;
use App\Form\Data\WorkTimeData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkTimeController extends AbstractController
{
    public function adder(Request $request): Response
    {
        $workTimeData = new WorkTimeData();
        $form = $this->createForm(WorkTimeDataType::class, $workTimeData);
        $form->handleRequest($request);

        return $this->render('employee/components/time_adder.html.twig', [
            'form' => $form
        ]);
    }
}
