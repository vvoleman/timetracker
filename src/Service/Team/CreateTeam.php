<?php

namespace App\Service\Team;

use App\Entity\Employer;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CreateTeam {

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function create(User $manager, string $name, array $members = [], array $employers = []) {
        $team = new Team();
        $team->setManager($manager);
        $team->setName($name);

        foreach($members as $member){
            if($member instanceof User){
                $team->addMember($member);
            }
        }
        foreach ($employers as $employer) {
            if($employer instanceof Employer){
                $team->addEmployer($employer);
            }
        }

        $this->entityManager->persist($team);
        $this->entityManager->flush();
    }

}