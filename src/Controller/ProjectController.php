<?php

namespace App\Controller;

use App\Repository\EmployerRepository;
use App\Repository\TeamRepository;
use App\Service\Project\ProjectService;
use App\Service\Util\ArraysTrait;
use App\Service\Util\JsonRequest;
use App\Service\Util\ResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/api/projects", name: "api_projects")]
class ProjectController extends AbstractController {

    use ResponseTrait;
    use ArraysTrait;

    private NormalizerInterface $normalizer;
    private TeamRepository $teamRepository;
    private EmployerRepository $employerRepository;

    public function __construct(NormalizerInterface $normalizer,
                                TeamRepository      $teamRepository,
                                EmployerRepository  $employerRepository) {
        $this->normalizer = $normalizer;
        $this->teamRepository = $teamRepository;
        $this->employerRepository = $employerRepository;
    }

    /**
     * Returns all projects of a team
     * @param Request $request
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    #[Route("/", name: "", methods: ["GET"])]
    public function list(Request $request): JsonResponse {
        //can user see team?
        $team = $this->teamRepository->find($request->get("teamId", ""));
        if (!$this->isGranted("TEAM_SEE", $team)) {
            return $this->error("You cannot access this team!", Response::HTTP_FORBIDDEN);
        }

        return $this->ok($this->normalizer->normalize([
            "team" => $team,
            "projects" => $team->getProjects()
        ], null, ["groups" => "publicProject"]));
    }

    /**
     * Creates a new project for a team
     * @param JsonRequest $jsonRequest
     * @param ProjectService $projectService
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    #[Route("/create",name:"_create",methods: ["POST"])]
    public function create(JsonRequest $jsonRequest, ProjectService $projectService): JsonResponse {
        $data = $this->doesExist($jsonRequest->getDataAsArray(), ["teamId", "name"]);
        $data["employerId"] = $jsonRequest->getDataAsArray()["employerId"];

        //can user see team?
        $team = $this->teamRepository->find($data["teamId"]);
        if (!$this->isGranted("TEAM_MANAGE", $team)) {
            return $this->error("You cannot update this team!", Response::HTTP_FORBIDDEN);
        }

        //is employer entered and valid?
        $employer = null;
        if ($data["employerId"]) {
            $employer = $this->employerRepository->find($data["employerId"]);
            if(!$employer){
                return $this->error(sprintf("Unknown employerId:%s",$data["employerId"]),Response::HTTP_NOT_FOUND);
            }
        }

        //new project
        $project = $projectService->create($team, $data["name"],$employer);

        return $this->ok($this->normalizer->normalize([
            "project" => $project
        ], null, ["groups" => "publicProject"]));
    }

}