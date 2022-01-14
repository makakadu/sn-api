<?php
declare(strict_types=1);
namespace App\Application\Common;

use App\Application\Exceptions\ValidationException;
use App\Application\Exceptions\MalformedRequestException;

interface RequestParamsValidator {
    /**
     * @throws MalformedRequestException if $value is not a string.
     */
    function string($value, string $message): void;
    
    /**
     * @throws MalformedRequestException if $value is not a boolean.
     */
    function boolean($value, string $message): void;
    
    /**
     * @throws ValidationException if length of $value is lesser than $minLength.
     */
    function minLength($value, int $minLength, string $message): void;
    
    /**
     * @throws ValidationException if length of $value is bigger than $maxLength.
     */
    function maxLength($value, int $maxLength, string $message): void;
    
    /**
     * @throws MalformedRequestException if $value is not a string.
     */
    function keyExists($array, $key, string $message): void;
    
    /**
     * @throws MalformedRequestException if $value is not a string.
     */
    function isArray($value, string $message): void;
    
    /**
     * @throws MalformedRequestException if $value is not a string.
     */
    function areAllArrays(array $values, string $message): void;
    
    /**
     * @throws MalformedRequestException if $value is not a string.
     */
    function keysExist($array, array $keys, string $message): void;
    
    /**
     * @throws MalformedRequestException if $value is not a string.
     */
    function inArray($value, array $array, string $message): void;
    
    function between($value, int $lowerLimit, int $upperLimit, string $message): void;
    
    /**
     * @throws ValidationException if length of $value is bigger than $upperLimit.
     */
    function arrayLength($value, int $upperLimit, string $message): void;
}