<?php

namespace App\Entity;

use App\Repository\EmployerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=EmployerRepository::class)
 */
class Employer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"publicEmployer","publicTeam","publicProject"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"publicEmployer","publicTeam","publicProject"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="employers")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"publicEmployer"})
     */
    private $madeBy;

    /**
     * @ORM\OneToMany(targetEntity=Project::class, mappedBy="employer")
     * @Groups({"publicEmployer"})
     */
    private $projects;

    public function __construct() {
        $this->id = Uuid::v4();
        $this->projects = new ArrayCollection();
    }

    #[Pure] public function getId(): string {
        return $this->id->toRfc4122();
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

    public function getMadeBy(): ?Team
    {
        return $this->madeBy;
    }

    public function setMadeBy(?Team $madeBy): self
    {
        $this->madeBy = $madeBy;

        return $this;
    }

    /**
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setEmployer($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getEmployer() === $this) {
                $project->setEmployer(null);
            }
        }

        return $this;
    }
}
