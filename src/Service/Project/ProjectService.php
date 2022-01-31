<?php

namespace App\Service\Project;

use App\Entity\Employer;
use App\Entity\Project;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;

class ProjectService {

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function create(Team $t, string $name, ?Employer $employer): Project {
        $project = new Project();
        $project->setTeam($t);
        $project->setName($name);

        if($employer){
            $project->setEmployer($employer);
        }

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $project;
    }

}