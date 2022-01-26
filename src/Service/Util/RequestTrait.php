<?php

namespace App\Service\Util;

use Symfony\Component\HttpFoundation\Request;

trait RequestTrait {

    public function retrieveKeys(Request $request, array $keys, $strict = true): array {
        $results = [];
        foreach ($keys as $key) {
            $temp = $request->get($key);
            if($strict && !$temp){
                throw new \InvalidArgumentException(sprintf("Required key %s not present",$key));
            }
             $results[$key] = $temp;
        }
        return $results;
    }

}