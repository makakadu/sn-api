<?php
declare(strict_types=1);
namespace App\Infrastructure\Application;

use Assert\Assertion;
use App\Application\Exceptions\ValidationException;
use App\Application\Exceptions\MalformedRequestException;
use App\Application\RequestParamsValidator;

class BeberleiRequestParamsValidator implements RequestParamsValidator {
    
    function string($value, string $message): void {
        try {
            Assertion::string($value);
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($message);
        }
    }
    
    function boolean($value, string $message): void {
        try {
            Assertion::boolean($value);
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($message);
        }
    }
    
    function minLength($value, int $minLength, string $message): void {
        try {
            Assertion::minLength($value, $minLength);
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new ValidationException($message);
        }
    }
    
    function maxLength($value, int $maxLength, string $message): void {
        try {
            Assertion::maxLength($value, $maxLength);
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new ValidationException($message);
        }
    }
    
    function keyExists($array, $key, string $message): void {
        try {
            Assertion::keyExists($array, $key);
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($message);
        }
    }
    
    function keysExist($array, array $keys, string $message): void {
        try {
            foreach ($keys as $key) {
//                print_r($keys);
//                echo $key;
                
                Assertion::keyExists($array, $key);
            }
            
        } catch (\Assert\InvalidArgumentException $ex) {
            //exit();
            throw new MalformedRequestException($message);
        }
    }
    
    function isArray($value, string $message): void {
        try {
            Assertion::isArray($value);
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($message);
        }
    }
    
    function areAllArrays(array $values, string $message): void {
        try {
            foreach($values as $value) {
                Assertion::isArray($value);
            }
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($message);
        }
    }
    
    function inArray($value, array $array, string $message): void {
        try {
            Assertion::inArray($value, $array);
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new ValidationException($message);
        }
    }
    
    function between($value, int $lowerLimit, int $upperLimit, string $message): void {
        try {
            Assertion::between($value, $lowerLimit, $upperLimit);
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new ValidationException($message);
        }
    }
    
    function arrayLength($value, int $upperLimit, string $message): void {
//        try {
//            Assertion::maxLength($value, $upperLimit);
//        } catch (\Assert\InvalidArgumentException $ex) {
//            throw new ValidationException($message);
//        }
    }
}