<?php
declare(strict_types=1);
namespace App\Application;

use Assert\Assertion;

class GetRequestParamsValidator {
    
    /** @param mixed $count */
    static function validateCountParam($count): void {
        if(!$count) {
            return;
        }
        try {
            Assertion::numeric($count, "Param 'count' should be numeric");
            Assertion::integer((int)$count, "Param 'count' should be integer");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new \App\Application\Exceptions\ValidationException($ex->getMessage());
        }
    }
    
    /** @param mixed $commentsCount */
    static function validateCommentsCountParam($commentsCount): void {
        if(!$commentsCount) {
            return;
        }
        try {
            Assertion::numeric($commentsCount, "Param 'comments-count' should be numeric");
            Assertion::integer((int)$commentsCount, "Param 'comments-count' should be integer");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new \App\Application\Exceptions\ValidationException($ex->getMessage());
        }
    }
    
    /** @param mixed $offsetId */
    static function validateOffsetIdParam($offsetId): void {
        if(!$offsetId) {
            return;
        }
        try {
            Assertion::string($offsetId, "Param 'offset-id' should be string");
            Assertion::length($offsetId, 26, "Param 'offset-id' should be ULID(26 characters long)");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new \App\Application\Exceptions\ValidationException($ex->getMessage());
        }
    }
    
    /** @param mixed $commentsType */
    static function validateCommentsTypeParam($commentsType): void {
        if(!$commentsType) {
            return;
        }
        try {
            \Assert\Assertion::string($commentsType, "Param 'comments-type' should be string");
            $value = \mb_strtolower($commentsType);
            \Assert\Assertion::inArray($value, ['root', 'all'], "Value of 'comments-type' param should be 'all' or 'root'");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new \App\Application\Exceptions\ValidationException($ex->getMessage());
        }
    }
    
    /** @param mixed $commentsOrder */
    static function validateCommentsOrderParam($commentsOrder, array $acceptedOrderingTypes): void {
        if(!$commentsOrder) {
            return;
        }
        try {
            $orderTypesString = \implode(', ', $acceptedOrderingTypes);
            \Assert\Assertion::string($commentsOrder, "Param 'comments-order' should be string");
            $value = \mb_strtolower($commentsOrder);
            \Assert\Assertion::inArray($value, $acceptedOrderingTypes, "Value of 'comments-order' param should be one of these: $orderTypesString");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new \App\Application\Exceptions\ValidationException($ex->getMessage());
        }
    }
    
    /** @param mixed $order */
    static function validateOrderParam($order, array $acceptedOrderingTypes): void {
        if(!$order) {
            return;
        }
        try {
            $orderTypesString = \implode(', ', $acceptedOrderingTypes);
            \Assert\Assertion::string($order, "Param 'order' should be string");
            $value = \mb_strtolower($order);
            \Assert\Assertion::inArray($value, $acceptedOrderingTypes, "Value of 'order' param should be one of these: $orderTypesString");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new \App\Application\Exceptions\ValidationException($ex->getMessage());
        }
    }
    
    
}