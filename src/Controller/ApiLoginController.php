<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\User\CreateUser;
use App\Service\User\RetrieveUserToken;
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

class ApiLoginController extends AbstractController {

    use ResponseTrait;

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    #[Route("/api/login", name: "api_login", methods: ["POST"])]
    public function index(Request $request, RetrieveUserToken $retrieve): Response {
        try {
            $email = $request->request->get('email', '');
            $token = $retrieve->getByCredentials($email, $request->request->get('password', ''));

            return $this->loginData($token);
        } catch (BadCredentialsException $e) {
            return $this->error("Invalid Credentials",Response::HTTP_UNAUTHORIZED);
        }
    }

    #[Route("/api/register", name: "api_register")]
    public function register(Request $request, CreateUser $createUser): Response {
        try {
            $user = $createUser->create(["email" => "vojtavol@email.cz", "password" => "heslo123"]);
        } catch (\Exception $e) {
            return $this->error("Unable to use this email",Response::HTTP_UNAUTHORIZED);
        }

        return $this->loginData($user->getDefaultToken());
    }
}
