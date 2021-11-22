<?php

namespace App\Service\User;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Repository\ApiTokenRepository;
use App\Service\Util\LoggerAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface as Hasher;

class CreateUser {

    use LoggerAwareTrait;

    private EntityManagerInterface $manager;
    private Hasher $hasher;
    private ApiTokenRepository $tokenRepository;

    public function __construct(EntityManagerInterface $manager, ApiTokenRepository $tokenRepository, Hasher $hasher) {
        $this->manager = $manager;
        $this->hasher = $hasher;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * @throws \Exception
     */
    public function create(array $data): User {
        $data = new ArrayCollection($data);

        $user = new User();
        $user->setEmail($data->get('email'));
        $user->setPassword($this->hasher->hashPassword($user, $data->get("password")));

        $token = new ApiToken();
        $token->setToken($this->tokenRepository->generateToken());
        $token->setUser($user);

        $user->setDefaultToken($token);

        $this->manager->persist($user);
        $this->manager->persist($token);

        try {
            $this->manager->flush();
        } catch (\Exception $e) {
            $this->getLogger()->error("Couldn't create user!",
                [
                    "exception" => $e,
                    "user" => $user
                ]);
            throw new \Exception("Already exists");
        }

        return $user;
    }

}