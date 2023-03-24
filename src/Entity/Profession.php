<?php

namespace App\Entity;

use App\Repository\ProfessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProfessionRepository::class)]
class Profession
{
    public const PAGE_SIZE = 10;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Ce champ ne peut pas être vide !')]
    #[Assert\Length(min: 2, minMessage: 'Le nom doit contenir au minimum {{ limit }} caractères !')]
    #[Assert\Length(max: 255, maxMessage: "Le nom doit contenir au maximum {{ limit }} caractères !")]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'profession', targetEntity: Employee::class)]
    private Collection $employees;

    public function __construct() 
    {
        $this->employees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Employee>
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(Employee $employee): self
    {
        if (!$this->employees->contains($employee)) {
            $this->employees->add($employee);
            $employee->setProfession($this);
        }

        return $this;
    }

    public function removeEmployee(Employee $employee): self
    {
        if ($this->employees->removeElement($employee)) {
            // set the owning side to null (unless already changed)
            if ($employee->getProfession() === $this) {
                $employee->setProfession(null);
            }
        }

        return $this;
    }
}
