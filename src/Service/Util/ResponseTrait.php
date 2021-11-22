<?php

namespace App\Service\Util;

use App\Entity\ApiToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ResponseTrait {

    public function ok(array $data, int $code = Response::HTTP_OK): JsonResponse {
        return new JsonResponse($data,$code);
    }

    public function error(string $message, int $code,array $data = []): JsonResponse {
        $data["message"] = $message;

        return new JsonResponse($data,$code);
    }

    /**
     * Returns data for login
     * @param ApiToken $token
     * @return JsonResponse
     */
    public function loginData(ApiToken $token): JsonResponse {
        return $this->ok(
            ["email" => $token->getUser()->getEmail(),
                "token" => [
                    "token" => $token->getToken(),
                    "expires_at" => $token->getExpiresAt()->getTimestamp()]
            ]
        );
    }

}