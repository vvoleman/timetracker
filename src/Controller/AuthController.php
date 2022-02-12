<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\User\CreateUser;
use App\Service\User\RetrieveUserToken;
use App\Service\Util\ArraysTrait;
use App\Service\Util\JsonRequest;
use App\Service\Util\ResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

#[Route("/api/auth", name: 'api_auth')]
class AuthController extends AbstractController {

    /** For unified return format */
    use ResponseTrait;

    /** Retrieve required keys from request */
    use ArraysTrait;

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    /**
     * <b>Login handler</b>
     * @param JsonRequest $request
     * @param RetrieveUserToken $retrieve
     * @return Response
     * @throws \Exception
     */
    #[Route("/login", name: "_login", methods: ["POST"])]
    public function login(JsonRequest $request, RetrieveUserToken $retrieve): Response {
        try {
            $email = $request->get("email","");
            $password = $request->get("password","");
            $token = $retrieve->getByCredentials($email, $password);

            return $this->loginData($token);
        } catch (BadCredentialsException $e) {
            return $this->error($e->getMessage()." Invalid Credentials", Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * <b>Register handler</b>
     * Registers user, requires email and password
     * @param Request $request
     * @param CreateUser $createUser
     * @return Response
     */
    #[Route("/register", name: "_register", methods: ["POST"])]
    public function register(Request $request, CreateUser $createUser): Response {
        try {
            $data = $this->doesExist($request->request->all(), ["email", "password"]);
            $user = $createUser->create($data);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return $this->error("Unable to use this email", Response::HTTP_UNAUTHORIZED);
        }

        return $this->loginData($user->getDefaultToken());
    }
}
