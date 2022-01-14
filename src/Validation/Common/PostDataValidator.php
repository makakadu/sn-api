<?php
declare(strict_types=1);
namespace App\Validation\Common;

use Assert\Assertion;
use App\Application\Exceptions\MalformedRequestException;

class PostDataValidator {

    
    /** @param mixed $value */
    static function validateParamDisableComments($value): void {
        try {
            Assertion::integer($value, "Param 'disable_comments' should be a integer");
            Assertion::between((int)$value, 0, 1, "Param 'disable_comments' should be 0 or 1");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
    
    /** @param mixed $value */
    static function validateParamDisableReactions($value): void {
        try {
            Assertion::integer($value, "Param 'disable_reactions' should be a integer");
            Assertion::between((int)$value, 0, 1, "Param 'disable_reactions' should be 0 or 1");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
    
    /** @param mixed $value */
    static function validateParamShared($value): void {
        try {
            Assertion::isArray($value, "Param 'shared' should be an array");
            Assertion::keyExists($value, 'id', "Shared data should contains 'id' key");
            Assertion::keyExists($value, 'type', "Shared data should contains 'type' key");
        }
        catch(\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
}