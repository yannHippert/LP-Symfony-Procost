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
}
