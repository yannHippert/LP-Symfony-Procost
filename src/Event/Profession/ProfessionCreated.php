<?php 

namespace App\Event\Profession;

use App\Entity\Profession;

final class ProfessionCreated
{
    public function __construct(
        private Profession $profession
    ) {}

    public function getProfession(): Profession
    {
        return $this->profession;
    }
}
