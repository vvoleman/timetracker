<?php

namespace App\Service\Team;

use App\Entity\Employer;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class TeamService {

    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private TeamRepository $teamRepository;

    public function __construct(EntityManagerInterface $entityManager,
                                UserRepository         $userRepository,
                                TeamRepository         $teamRepository) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * Creates a team
     * @param User $manager
     * @param string $name
     * @param array $members
     * @param array $employers
     * @return Team
     */
    public function create(User $manager, string $name, array $members = [], array $employers = []): Team {
        $team = new Team();
        $team->setManager($manager);
        $team->setName($name);

        foreach ($members as $member) {
            if ($member instanceof User) {
                $team->addMember($member);
            }
        }
        foreach ($employers as $employer) {
            if ($employer instanceof Employer) {
                $team->addEmployer($employer);
            }
        }

        $this->entityManager->persist($team);

        $this->entityManager->flush();

        return $team;
    }

    /**
     * Changes details of a team
     * @param Team $t
     * @param array $changes
     */
    public function editDetails(Team $t, array $changes) {
        foreach ($changes as $key => $change) {
            switch ($key) {
                case "name":
                    $t->setName($change);
                    break;
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Adds members to a team
     * @param Team $t
     * @param array $ids
     */
    public function addMembers(Team $t, array $ids) {
        $users = $this->getUsers($ids);
        foreach ($users as $user) {
            $t->addMember($user);
        }
        $this->entityManager->flush();
    }

    /**
     * Deletes members of a team
     * @param Team $t
     * @param array $ids
     */
    public function deleteMembers(Team $t, array $ids) {
        $users = $this->getUsers($ids);
        foreach ($users as $user) {
            $t->removeMember($user);
        }
        $this->entityManager->flush();
    }

    /**
     * Changes a manager of a team
     * @throws EntityNotFoundException
     */
    public function changeManager(Team $t, string $id) {
        $user = $this->userRepository->find($id);
        if(!$user){
            throw new EntityNotFoundException(sprintf("Couldn't find an entity User with ID:%s",$id));
        }
        $t->setManager($user);
    }

    /**
     * Returns a team for an id
     * @param string $id
     * @return Team|null
     */
    public function getTeam(string $id): ?Team {
        return $this->teamRepository->find($id);
    }

    // --- | Private | ---

    /**
     * Returns an array of Users for an array of IDs
     * @param array $ids
     * @return array
     */
    private function getUsers(array $ids): array {
        $users = [];
        foreach ($ids as $id){
            $users[] = $this->userRepository->find($id);
        }
        return $users;
    }

}