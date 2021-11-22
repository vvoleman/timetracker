<?php

namespace App\Service\User;

use App\Entity\ApiToken;
use App\Repository\ApiTokenRepository;
use App\Repository\UserRepository;
use App\Service\Util\LoggerAwareTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class RetrieveUserToken {

    use LoggerAwareTrait;

    private UserRepository $userRepository;
    private UserPasswordHasherInterface $encoder;
    private ApiTokenRepository $tokenRepository;
    private EntityManagerInterface $manager;

    public function __construct(UserRepository              $userRepository,
                                UserPasswordHasherInterface $encoder,
                                ApiTokenRepository          $tokenRepository,
                                EntityManagerInterface      $manager
    ) {
        $this->userRepository = $userRepository;
        $this->encoder = $encoder;
        $this->tokenRepository = $tokenRepository;
        $this->manager = $manager;
    }

    /**
     * Returns ApiToken by credentials
     * @param string $email
     * @param string $plainPassword
     * @return ApiToken|null
     * @throws \Exception
     */
    public function getByCredentials(string $email, string $plainPassword): ?ApiToken {
        $user = $this->userRepository->findOneBy(["email" => $email]);
        if ($user) {
            if ($this->encoder->isPasswordValid($user, $plainPassword)) {
                //Does user have a default token?
                if (!$user->getDefaultToken()) {
                    //New token
                    $token = new ApiToken();
                    $token->setToken($this->tokenRepository->generateToken());
                    $token->setUser($user);
                    $this->manager->persist($token);
                    $this->manager->flush();

                    $user->setDefaultToken($token);
                }

                return $user->getDefaultToken();
            }

        }
        throw new BadCredentialsException();
    }

}