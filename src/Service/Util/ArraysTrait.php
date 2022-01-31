<?php

namespace App\Service\Util;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

trait ArraysTrait {

    /**
     * Check if every key from <b>$keys</b> exists, returns array of values if so, otherwise throws an
     * <b>InvalidArgumentException</b><br>
     * This behaviour can changed with <b>$strict</b> parameter. If it's true, then an exception is thrown, if false,
     * it just continues
     * @param array $data Data to be checked
     * @param array $keys Required keys
     * @param bool $strict Should throw an exception on miss?
     * @return array Key-value array
     * @throws InvalidArgumentException Required key not found
     */
    public function doesExist(array $data, array $keys, bool $strict = true): array {
        $results = [];
        foreach ($keys as $key) {
            $temp = $data[$key];
            if($strict && !isset($temp)){
                throw new InvalidArgumentException(sprintf("Required key %s not present",$key));
            }
             $results[$key] = $temp;
        }
        return $results;
    }

}