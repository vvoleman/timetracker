<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Util\ResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/api/teams",name:"api_teams")]
class TeamController extends AbstractController {

    use ResponseTrait;

    #[Route("/",name:"_list",methods: ["GET"])]
    public function list(): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        return $this->ok([
            "teams"=>$user->getTeams()
        ]);
    }

    public function create(Request $request){
        /** @var User $user */
        $user = $this->getUser();
        $data = [];
        //TODO: Dodělat create team
    }

}