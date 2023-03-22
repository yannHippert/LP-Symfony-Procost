<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    public const PAGE_SIZE = 10;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deliveredAt = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Worktime::class)]
    private Collection $worktimes;

    public function __construct()
    {
        $this->worktimes = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
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

    public function getDeliveredAt(): ?\DateTimeImmutable
    {
        return $this->deliveredAt;
    }

    public function setDeliveredAt(?\DateTime $deliveredAt): self
    {
        $this->deliveredAt = new \DateTimeImmutable($deliveredAt->format("Y-M-d H:m:s"));

        return $this;
    }

    /**
     * @return Collection<int, Worktime>
     */
    public function getworktimes(): Collection
    {
        return $this->worktimes;
    }

    public function addWorktime(Worktime $worktime): self
    {
        if (!$this->worktimes->contains($worktime)) {
            $this->worktimes->add($worktime);
            $worktime->setProject($this);
        }

        return $this;
    }

    public function removeWorktime(Worktime $worktime): self
    {
        if ($this->worktimes->removeElement($worktime)) {
            // set the owning side to null (unless already changed)
            if ($worktime->getProject() === $this) {
                $worktime->setProject(null);
            }
        }

        return $this;
    }

    public function productionCost(): float
    {
        $cost = 0;
        foreach ($this->worktimes as $worktime) {
            $cost += $worktime->getTotalPrice();
        }
        return $cost;
    }
}
