<?php
declare(strict_types=1);
namespace App\Application\Users\Posts;

use Assert\Assertion;
use App\Application\Exceptions\MalformedRequestException;

class PostParamsValidator extends \App\Application\Common\PostParamsValidator {

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
        try {
            Assertion::isArray($value, "Attachments should be an array");
            if(count($value) > 10) {
                throw new \App\Application\Exceptions\ValidationException("No more than 10 attachments allowed in post");
            }
            foreach ($value as $attachment) {
                Assertion::isArray($attachment, "Attachment data should be an array");
                Assertion::keyExists($attachment['id'], "Attachment data should contains 'id' key");
                Assertion::keyExists($attachment['type'], "Attachment data should contains 'type' key");
                Assertion::string($attachment['id'], "Property id in attachment data should be a string");
                Assertion::string($attachment['type'], "Property type in attachment data should be a string");
            }
        } catch(\Assert\InvalidArgumentException $ex) {
            //throw new MalformedRequestException($ex->getMessage());
            throw new ValidationException($ex->getMessage());
        }
    }
}