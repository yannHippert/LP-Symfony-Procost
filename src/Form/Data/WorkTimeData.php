<?php 

namespace App\Form\Data;

use App\Entity\Project;
use Symfony\Component\Validator\Constraints as Assert;

class WorkTimeData
{
    private Project $project;

    #[Assert\NotBlank(message: 'Ce champ ne peut pas être vide !')]
    #[Assert\Type(type: 'integer', message: 'Cette valeur doit être un entier positive !')]
    #[Assert\Positive(message: 'Cette valeur doit être un entier positive !')]
    private int $daysSpent;

    function __construct() {}

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getDaysSpent(): int
    {
        return $this->daysSpent;
    }

    public function setDaysSpent(int $daysSpent): self
    {
        $this->daysSpent = $daysSpent;

        return $this;
    }
}