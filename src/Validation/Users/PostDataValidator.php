<?php
declare(strict_types=1);
namespace App\Validation\Users;

use Assert\Assertion;
use App\Application\Exceptions\MalformedRequestException;
use App\Application\Exceptions\ValidationException;

class PostDataValidator extends \App\Validation\Common\PostDataValidator {

    /** @param mixed $value */
    static function validateParamText($value): void {
        try {
            Assertion::string($value, "Param 'text' should be a string");
            Assertion::maxLength($value, 500, "Text of post should have no more than 500 symbols");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new ValidationException($ex->getMessage());
        }
    }

    
    /** @param mixed $value */
    static function validateParamAttachments($value): void {
        //print_r($value);exit();
        try {
            
            Assertion::isArray($value, "Attachments should be an array");
            if(count($value) > 10) {
                throw new \App\Application\Exceptions\ValidationException("No more than 10 attachments allowed in post");
            }
            foreach ($value as $attachment) {
                //Assertion::isArray($attachment, "Attachment data should be an array");
//                Assertion::keyExists($attachment['id'], "Attachment data should contains 'id' key");
//                Assertion::keyExists($attachment['type'], "Attachment data should contains 'type' key");
//                Assertion::string($attachment['id'], "Property id in attachment data should be a string");
//                Assertion::string($attachment['type'], "Property type in attachment data should be a string");
            }
        } catch(\Assert\InvalidArgumentException $ex) {
            //throw new MalformedRequestException($ex->getMessage());
            throw new ValidationException($ex->getMessage());
        }
    }
    
    /** @param mixed $value */
    static function validateParamIsPublic($value): void {
        try {
            Assertion::integer($value, "Param 'is_public' should be a integer");
            Assertion::between((int)$value, 0, 1, "Param 'is_public' should be 0 or 1");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
}