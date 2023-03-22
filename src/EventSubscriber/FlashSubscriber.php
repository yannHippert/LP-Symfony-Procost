<?php 

namespace App\EventSubscriber;

use App\Event\EmployeeCreated;
use App\Event\EmployeeUpdated;
use App\Event\WorktimeCreated;
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
            EmployeeCreated::class => [
                ["onEmployeeCreated"]
            ],
            EmployeeUpdated::class => [
                ["onEmployeeUpdated"]
            ],
            WorktimeCreated::class => [
                ["onWorktimeCreated"]
            ]
        ];
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