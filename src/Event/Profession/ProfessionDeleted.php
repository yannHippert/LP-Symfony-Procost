<?php 

namespace App\Event\Profession;

use App\Entity\Profession;

final class ProfessionDeleted
{
    public function __construct(
        private Profession $profession
    ) {}

    public function getProfession(): Profession
    {
        return $this->profession;
    }
}
