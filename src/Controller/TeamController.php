<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\User;
use App\Repository\EmployerRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\Team\TeamService;
use App\Service\Util\ArraysTrait;
use App\Service\Util\JsonRequest;
use App\Service\Util\ResponseTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/api/teams", name: "api_teams")]
class TeamController extends AbstractController {
    use ResponseTrait;
    use ArraysTrait;

    private NormalizerInterface $normalizer;

    public function __construct(NormalizerInterface $normalizer) {
        $this->normalizer = $normalizer;
    }

    #[Route("/debug", name: "_debug", methods: ["GET"])]
    public function debug(UserRepository $userRepository, TeamRepository $teamRepository): JsonResponse {
        /** @var User $user */

        $allTeams = $teamRepository->findAll();
        $allUsers = $userRepository->findAll();
        return $this->ok([
            "users" => array_map(function ($x) {
                /** @var User $x */
                return $x->getId();
            }, $allUsers),
            "teams" => array_map(function ($x) {
                /** @var Team $x */
                return $x->getId();
            }, $allTeams)
        ]);
    }

    /**
     * Returns list of teams of user
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    #[Route("/", name: "_list", methods: ["GET"])]
    public function list(UserRepository $repository): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        return $this->ok(["teams" => $this->normalizer->normalize(
            $user->getTeams(), null,
            ["groups" => "publicTeam"])]);
    }

    /**
     * Creates team
     * @param JsonRequest $jsonRequest
     * @param TeamService $createTeam
     * @param UserRepository $userRepository
     * @param EmployerRepository $employerRepository
     * @return JsonResponse
     */
    #[
        Route("/create", name: "_create", methods: ["POST"])]
    public function create(JsonRequest        $jsonRequest,
                           TeamService        $createTeam,
                           UserRepository     $userRepository,
                           EmployerRepository $employerRepository
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $data = $this->doesExist($jsonRequest->getDataAsArray(), ["name", "members", "employers"]);

        //IDs to Entities
        try {
            $string = "Members";
            $data["members"] = $this->idsToEntities($userRepository, $data["members"]);
            $string = "Employers";
            $data["employers"] = $this->idsToEntities($employerRepository, $data["employers"]);
        } catch (EntityNotFoundException $exception) {
            return $this->error(sprintf("%s: %s", $string, $exception->getMessage()), Response::HTTP_NOT_FOUND);
        }

        $team = $createTeam->create($user, $data["name"], $data["members"], $data["employers"]);

        return $this->ok([
            "team" => $this->normalizer->normalize($team, null, [
                "groups" => "publicTeam"
            ])
        ]);
    }

    #[Route("/update", name: "_update", methods: ["PATCH"])]
    public function update(#[CurrentUser] User $user, JsonRequest $jsonRequest, TeamService $service): JsonResponse {
        $data = $this->doesExist(
            $jsonRequest->getDataAsArray(),
            ["teamId", "deleteMembers", "addMembers","changeManager"],
            false
        );

        //does team exists?
        $team = $service->getTeam($data["teamId"]);
        if(!$this->isGranted("TEAM_MANAGE",$team)){
            return $this->error("You cannot update this team!",Response::HTTP_FORBIDDEN);
        }

        //deleteMembers
        if(isset($data["deleteMembers"])){
            $service->deleteMembers($team, $data["deleteMembers"]);
        }

        //addMembers
        if(isset($data["addMembers"])){
            $service->addMembers($team, $data["addMembers"]);
        }

        //changeManager
        if(isset($data["changeManager"])){
            try {
                $service->changeManager($team, $data["changeManager"]);
            } catch (EntityNotFoundException $e) {
                return $this->error(sprintf("Unknown manager of ID:%s",$data["changeManager"]),Response::HTTP_NOT_FOUND);
            }
        }

        return $this->ok([
            "team" => $this->normalizer->normalize($team, null, [
                "groups" => "publicTeam"
            ])
        ]);
    }

    /**
     * Returns entities for entered IDs. If entity is not found, an exception is thrown
     * @param ServiceEntityRepository $repository Repository used to find entity. Only find() method is used
     * @throws EntityNotFoundException
     */
    private function idsToEntities(ServiceEntityRepository $repository, $ids) {
        for ($i = 0; $i < sizeof($ids); $i++) {
            $entity = $repository->find($ids[$i]);
            if (!$entity) {
                throw new EntityNotFoundException(
                    sprintf("Couldn't find an ID:%d",
                        $ids[$i])
                );
            }
            $ids[$i] = $entity;
        }
        return $ids;
    }

}