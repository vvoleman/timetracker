<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\User\CreateUser;
use App\Service\User\RetrieveUserToken;
use App\Service\Util\RequestTrait;
use App\Service\Util\ResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route("/api",name:'api_auth')]
class AuthController extends AbstractController {

    /** For unified return format */
    use ResponseTrait;
    /** Retrieve required keys from request */
    use RequestTrait;

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    /**
     * <b>Login handler</b>
     * Returns token of user, requires email and password
     * @param Request $request
     * @param RetrieveUserToken $retrieve
     * @return Response
     * @throws \Exception
     */
    #[Route("/login", name: "_login", methods: ["POST"])]
    public function login(Request $request, RetrieveUserToken $retrieve): Response {
        try {
            $email = $request->request->get('email', '');
            $token = $retrieve->getByCredentials($email, $request->request->get('password', ''));

            return $this->loginData($token);
        } catch (BadCredentialsException $e) {
            return $this->error("Invalid Credentials",Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * <b>Register handler</b>
     * Registers user, requires email and password
     * @param Request $request
     * @param CreateUser $createUser
     * @return Response
     */
    #[Route("/register", name: "_register",methods: ["POST"])]
    public function register(Request $request, CreateUser $createUser): Response {

        try {
            $data = $this->retrieveKeys($request, ["email", "password"]);
            $user = $createUser->create($data);
        }catch (\InvalidArgumentException $e){
            return $this->error($e->getMessage(),Response::HTTP_UNAUTHORIZED);
        }catch (\Exception $e) {
            return $this->error("Unable to use this email",Response::HTTP_UNAUTHORIZED);
        }

        return $this->loginData($user->getDefaultToken());
    }
}
