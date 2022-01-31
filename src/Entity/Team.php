<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=TeamRepository::class)
 */
class Team
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"allTeam","publicTeam","publicUser","publicProject","publicEmployer"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"allTeam","publicTeam","publicUser","publicProject","publicEmployer"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="teams")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"allTeam","publicTeam"})
     */
    private $manager;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="teams")
     * @Groups({"allTeam","publicTeam"})
     */
    private $members;

    /**
     * @ORM\OneToMany(targetEntity=Employer::class, mappedBy="madeBy")
     * @Groups({"allTeam","publicTeam"})
     */
    private $employers;

    /**
     * @ORM\OneToMany(targetEntity=Project::class, mappedBy="team")
     * @Groups({"allTeam","publicTeam"})
     */
    private $projects;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->employers = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->id = Uuid::v4();

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

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
        }

        return $this;
    }

    public function removeMember(User $member): self
    {
        $this->members->removeElement($member);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getEmployers(): Collection
    {
        return $this->employers;
    }

    public function addEmployer(Employer $employer): self
    {
        if (!$this->employers->contains($employer)) {
            $this->employers[] = $employer;
            $employer->setMadeBy($this);
        }

        return $this;
    }

    public function removeEmployer(Employer $employer): self
    {
        if ($this->employers->removeElement($employer)) {
            // set the owning side to null (unless already changed)
            if ($employer->getMadeBy() === $this) {
                $employer->setMadeBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setTeam($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getTeam() === $this) {
                $project->setTeam(null);
            }
        }

        return $this;
    }

}
