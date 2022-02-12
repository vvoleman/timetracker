<?php

namespace App\Service\Util;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class JsonRequest {

    private Request $request;
    private \stdClass $data;
    private array $arrayData;

    public function __construct(RequestStack $requestStack) {
        $this->request = $requestStack->getCurrentRequest();
        $this->arrayData = json_decode($this->request->getContent(),true);
    }

    /**
     * Returns original request
     * @return Request
     */
    public function getRequest(): Request {
        return $this->request;
    }

    /**
     * Returns JSON data from request
     * @return mixed+
     */
    public function getDataAsObject(): \stdClass {
        if (!isset($this->data)) {
            $this->data = json_decode(json_encode($this->arrayData));
        }

        return $this->data;
    }

    public function getDataAsArray(): array {
        return $this->arrayData;
    }

    public function get(string $key, string $default = null){
        if(array_key_exists($key,$this->arrayData)){
            return $this->arrayData[$key];
        }
        return $default;
    }


}