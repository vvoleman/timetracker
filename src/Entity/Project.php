<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 */
class Project {

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"publicProject","publicTeam","publicEmployer"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"publicProject","publicTeam","publicEmployer"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="projects")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"publicProject"})
     */
    private $team;

    /**
     * @ORM\ManyToOne(targetEntity=Employer::class, inversedBy="projects")
     * @ORM\JoinColumn(onDelete="SET NULL",nullable=true)
     * @Groups({"publicProject"})
     */
    private $employer;

    public function __construct() {
        $this->id = Uuid::v4();
    }

    #[Pure] public function getId(): string {
        return $this->id->toRfc4122();
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;

        return $this;
    }

    public function getTeam(): ?Team {
        return $this->team;
    }

    public function setTeam(?Team $team): self {
        $this->team = $team;

        return $this;
    }

    public function getEmployer(): ?Employer
    {
        return $this->employer;
    }

    public function setEmployer(?Employer $employer): self
    {
        $this->employer = $employer;

        return $this;
    }
}
