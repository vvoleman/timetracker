<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface {

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"secret","publicUser","publicTeam"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"secret","publicUser","publicTeam"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"secret"})
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Groups({"secret"})
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=ApiToken::class, mappedBy="user", orphanRemoval=true)
     * @Groups({"secret"})
     */
    private $apiTokens;

    /**
     * @ORM\OneToOne(targetEntity=ApiToken::class)
     * @ORM\JoinColumn(name="default_token_id",onDelete="SET NULL",nullable=true)
     * @Groups({"secret"})
     */
    private $default_token;

    /**
     * @ORM\OneToMany(targetEntity=Team::class, mappedBy="manager")
     * @ORM\JoinColumn(nullable=false,onDelete="SET NULL")
     * @Groups({"publicUser"})
     */
    private $teams;

    public function __construct() {
        $this->apiTokens = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->id = Uuid::v4();
    }

    #[Pure] public function getId(): string {
        return $this->id->toRfc4122();
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(string $email): self {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string {
        return (string)$this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): self {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials() {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|ApiToken[]
     */
    public function getApiTokens(): Collection {
        return $this->apiTokens;
    }

    public function addApiToken(ApiToken $apiToken): self {
        if (!$this->apiTokens->contains($apiToken)) {
            $this->apiTokens[] = $apiToken;
            $apiToken->setUser($this);
        }

        return $this;
    }

    public function removeApiToken(ApiToken $apiToken): self {
        if ($this->apiTokens->removeElement($apiToken)) {
            // set the owning side to null (unless already changed)
            if ($apiToken->getUser() === $this) {
                $apiToken->setUser(null);
            }
        }

        return $this;
    }

    public function getDefaultToken(): ?ApiToken {
        return $this->default_token;
    }

    public function setDefaultToken(?ApiToken $default_token): self {
        $this->default_token = $default_token;

        return $this;
    }

    /**
     * @return Collection|Team[]
     */
    public function getManagingTeam(): Collection {
        return $this->teams;
    }

    public function addTeamToManage(Team $team): self {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
            $team->setManager($this);
        }

        return $this;
    }

    public function removeTeamToManage(Team $team): self {
        if ($this->teams->removeElement($team)) {
            // set the owning side to null (unless already changed)
            if ($team->getManager() === $this) {
                $team->setManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Team[]
     */
    public function getTeams(): Collection {
        return $this->teams;
    }

    public function addTeam(Team $team): self {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
            $team->addMember($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): self {
        if ($this->teams->removeElement($team)) {
            $team->removeMember($this);
        }

        return $this;
    }
}
