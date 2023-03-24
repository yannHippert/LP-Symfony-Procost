<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    public const PAGE_SIZE = 10;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Ce champ ne peut pas être vide !')]
    #[Assert\Length(min: 2, minMessage: 'Le prénom doit contenir au minimum {{ limit }} caractères !')]
    #[Assert\Length(max: 255, maxMessage: "Le prénom doit contenir au maximum {{ limit }} caractères !")]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Ce champ ne peut pas être vide !')]
    #[Assert\Length(min: 2, minMessage: 'Le nom doit contenir au minimum {{ limit }} caractères !')]
    #[Assert\Length(max: 255, maxMessage: 'Le nom doit contenir au maximum {{ limit }} caractères !')]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Ce champ ne peut pas être vide !')]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide !')]
    #[Assert\Length(max: 255, maxMessage: 'L\'email doit contenir au maximum {{ limit }} caractères !')]
    private ?string $email = null;

    #[ORM\ManyToOne(inversedBy: 'employees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Profession $profession = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Ce champ ne peut pas être vide !')]
    #[Assert\Positive(message: 'Le salaire journalier ne peux pas être negatif !')]
    private ?float $dailySalary = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank(message: 'Ce champ ne peut pas être vide !')]
    #[Assert\Type("\DateTimeInterface", message: 'Ce champ doit être une date !')]
    #[Assert\LessThanOrEqual('today', message: "La date d'embauche ne peux pas être supérieur à la date actuelle !")]
    private ?\DateTimeImmutable $employmentDate = null;

    #[ORM\OneToMany(mappedBy: 'employee', targetEntity: Worktime::class)]
    private Collection $worktimes;

    public function __construct() 
    {
        $this->employmentDate = new \DateTimeImmutable();
        $this->worktimes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getProfession(): ?Profession
    {
        return $this->profession;
    }

    public function setProfession(?Profession $profession): self
    {
        $this->profession = $profession;

        return $this;
    }

    public function getDailySalary(): ?float
    {
        return $this->dailySalary;
    }

    public function setDailySalary(float $dailySalary): self
    {
        $this->dailySalary = $dailySalary;

        return $this;
    }

    public function getEmploymentDate(): ?\DateTimeImmutable
    {
        return $this->employmentDate;
    }

    public function setEmploymentDate(\DateTime $employmentDate): self
    {
        $this->employmentDate = new \DateTimeImmutable($employmentDate->format("Y-M-d H:m:s"));

        return $this;
    }

    /**
     * @return Collection<int, Worktime>
     */
    public function getworktimes(): Collection
    {
        return $this->worktimes;
    }

    public function addWorktime(Worktime $workTime): self
    {
        if (!$this->worktimes->contains($workTime)) {
            $this->worktimes->add($workTime);
            $workTime->setEmployee($this);
        }

        return $this;
    }

    public function removeWorktime(Worktime $workTime): self
    {
        if ($this->worktimes->removeElement($workTime)) {
            // set the owning side to null (unless already changed)
            if ($workTime->getEmployee() === $this) {
                $workTime->setEmployee(null);
            }
        }

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->firstName . " " . strtoupper($this->lastName);
    }

    public function getTotalCost(): int 
    {
        $cost = 0;
        foreach ($this->worktimes as $worktime) {
            $cost += $worktime->getDaysSpent() * $this->dailySalary;
        }
        return $cost;   
    }
}
