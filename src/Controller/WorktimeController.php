<?php

namespace App\Controller;

use App\Entity\Worktime;
use App\EventManager\WorktimeManager;
use App\Repository\WorktimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class WorktimeController extends AbstractController
{
    public function __construct(
        private WorktimeManager $worktimeManager,
        private WorktimeRepository $worktimeRepository
    ) {}

    public function listLatest(): Response
    {
        $latestWorktimes = $this->worktimeRepository->getLatest(6);

        return $this->render('main/components/_worktimes_list.html.twig', [
            'worktimes' => $latestWorktimes,
            'title' => 'Temps de production'
        ]);
    }

}
