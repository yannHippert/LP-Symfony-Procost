<?php

namespace App\Entity;

use App\Form\Data\WorkTimeData;
use App\Repository\WorkTimeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkTimeRepository::class)]
class WorkTime
{
    public const PAGE_SIZE = 10;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'workTimes')]
    private Employee $employee;

    #[ORM\ManyToOne(inversedBy: 'workTimes')]
    private Project $project;

    #[ORM\Column]
    private int $daysSpent;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        WorkTimeData $workTimeData,
        Employee $employee
    ) {
        $this->daysSpent = $workTimeData->getDaysSpent();
        $this->project = $workTimeData->getProject();
        $this->employee = $employee;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(?Employee $employee): self
    {
        $this->employee = $employee;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getDaysSpent(): ?int
    {
        return $this->daysSpent;
    }

    public function setDaysSpent(?int $daysSpent): self
    {
        $this->daysSpent = $daysSpent;

        return $this;
    }

    public function getTotalPrice(): float
    {
        return $this->daysSpent * $this->employee->getDailySalary();
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = new \DateTimeImmutable($createdAt->format("Y-M-d H:m:s"));

        return $this;
    }
}
