<?php 

namespace App\EventSubscriber;

use App\Event\Employee\EmployeeCreated;
use App\Event\Employee\EmployeeUpdated;
use App\Event\Profession\ProfessionCreated;
use App\Event\Profession\ProfessionDeleted;
use App\Event\Profession\ProfessionUpdated;
use App\Event\Project\ProjectCreated;
use App\Event\Project\ProjectDelivered;
use App\Event\Project\ProjectUpdated;
use App\Event\Worktime\WorktimeCreated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

final class FlashSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $stack
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectCreated::class => [
                ["onProjectCreated"]
            ],
            ProjectUpdated::class => [
                ["onProjectUpdated"]
            ],
            ProjectDelivered::class => [
                ["onProjectDelivered"]
            ],
            EmployeeCreated::class => [
                ["onEmployeeCreated"]
            ],
            EmployeeUpdated::class => [
                ["onEmployeeUpdated"]
            ],
            WorktimeCreated::class => [
                ["onWorktimeCreated"]
            ],
            ProfessionCreated::class => [
                ["onProfessionCreated"]
            ],
            ProfessionUpdated::class => [
                ["onProfessionUpdated"]
            ],
            ProfessionDeleted::class => [
                ["onProfessionDeleted"]
            ]
        ];
    }

    public function onProjectCreated(ProjectCreated $payload): void 
    {
        $this->addFlash('success', "Le projet {$payload->getProject()->getName()} a été créé !");
    }

    public function onProjectUpdated(ProjectUpdated $payload): void 
    {
        $this->addFlash('success', "Le projet {$payload->getProject()->getName()} a été modifié !");
    }

    public function onProjectDelivered(ProjectDelivered $payload): void 
    {
        $this->addFlash('success', "Le projet {$payload->getProject()->getName()} a été livré !");
    }

    public function onEmployeeCreated(EmployeeCreated $payload): void 
    {
        $this->addFlash('success', "L'employé {$payload->getEmployee()->getFullName()} a été créé !");
    }

    public function onEmployeeUpdated(EmployeeUpdated $payload): void 
    {
        $this->addFlash('success', "L'employé {$payload->getEmployee()->getFullName()} a été modifié !");
    }

    public function onWorktimeCreated(WorktimeCreated $payload): void 
    {
        $worktime = $payload->getWorktime();
        $this->addFlash('success', "Temps de production de {$worktime->getDaysSpent()} jours ajouté pour {$worktime->getEmployee()->getFullName()} sur le projet {$worktime->getProject()->getName()} !");
    }

    public function onProfessionCreated(ProfessionCreated $payload): void 
    {
        $this->addFlash('success', "Le métier {$payload->getProfession()->getName()} a été créé !");
    }

    public function onProfessionUpdated(ProfessionUpdated $payload): void 
    {
        $this->addFlash('success', "Le métier {$payload->getProfession()->getName()} a été modifié !");
    }

    public function onProfessionDeleted(ProfessionDeleted $payload): void 
    {
        $this->addFlash('success', "Le métier {$payload->getProfession()->getName()} a été suprimé !");
    }

    private function addFlash(String $flashType, String $flashMessage)
    {
        try {
            $session = $this->stack->getSession();
            if (!$session instanceof Session) return;
            $session->getFlashBag()->add($flashType, $flashMessage);
        } catch (SessionNotFoundException $e) {
            throw new \LogicException('You cannot use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".', 0, $e);
        }
    }
}