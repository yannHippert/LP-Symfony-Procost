<?php 

namespace App\Event;

use App\Entity\Profession;

final class ProfessionUpdated
{
    public function __construct(
        private Profession $profession
    ) {}

    public function getProfession(): Profession
    {
        return $this->profession;
    }
}