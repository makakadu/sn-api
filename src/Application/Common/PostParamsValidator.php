<?php
declare(strict_types=1);
namespace App\Application\Common;

use Assert\Assertion;
use App\Application\Exceptions\MalformedRequestException;

class PostParamsValidator {
    
    /** @param mixed $value */
    static function validateParamDisableComments($value): void {
        try {
            Assertion::integer($value, "Param 'disable_comments' should be a integer");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
    
    /** @param mixed $value */
    static function validateParamDisableReactions($value): void {
        try {
            Assertion::integer($value, "Param 'disable_reactions' should be a integer");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
    
    /** @param mixed $value */
    static function validateParamShared($value): void {
        try {
            Assertion::isArray($value, "Param 'shared' should be an array");
            foreach ($value as $attachment) {
                Assertion::isArray($attachment, "Shared data should be an array");
                Assertion::keyExists($attachment['id'], "Shared data should contains 'id' key");
                Assertion::keyExists($attachment['type'], "Shared data should contains 'type' key");
                Assertion::string($attachment['id'], "Property id in shared data should be a string");
                Assertion::string($attachment['type'], "Property type in shared data should be a string");
            }
        } catch(\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
}