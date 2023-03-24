<?php 

namespace App\EventManager;

use App\Entity\Profession;
use App\Event\ProfessionCreated;
use App\Event\ProfessionDeleted;
use App\Event\ProfessionUpdated;
use App\Repository\ProfessionRepository;
use Psr\EventDispatcher\EventDispatcherInterface;

final class ProfessionManager
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private ProfessionRepository $professionRepository,
    ) {}

    public function addProfession(Profession $profession): void
    {
        $this->professionRepository->save($profession, true);

        $this->eventDispatcher->dispatch(new ProfessionCreated($profession));
    }

    public function updateProfession(Profession $profession): void
    {
        $this->professionRepository->save($profession, true);

        $this->eventDispatcher->dispatch(new ProfessionUpdated($profession));
    }

    public function deleteProfession(Profession $profession): void
    {
        $this->professionRepository->remove($profession, true);

        $this->eventDispatcher->dispatch(new ProfessionDeleted($profession));
    }
}
