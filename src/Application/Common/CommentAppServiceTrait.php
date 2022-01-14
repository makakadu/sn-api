<?php
declare(strict_types=1);
namespace App\Application\Common;

use App\Application\Exceptions\MalformedRequestException;
use Assert\Assertion;

trait CommentAppServiceTrait {
    
    /**
     * @param array<mixed> $payload
     * @throws MalformedRequestException
     */
    function validateCommentPayload(array $payload): void {
        foreach($payload as $field => $value) {
            switch($field) {
                case self::TEXT:
                    $this->validateCommentText($value); break;
                default:
                    throw new MalformedRequestException(
                        "Wrong param was passed, resource does not have '$field' property"
                    );
            }
        }
    }
    /**
     * @param mixed $value
     */
    protected function validateCommentText($value): void {
        Assertion::string($value, "Text of comment should be a string");
        Assertion::maxLength($value, 500, "Text of comment should have no more than 500 symbols");
    }
}