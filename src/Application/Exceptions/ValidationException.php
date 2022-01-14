<?php
declare(strict_types=1);
namespace App\Application\Exceptions;

class ValidationException extends ApplicationException {
    protected int $statusCode = 422;

    function __construct(string $message) {
        parent::__construct(['code' => 228, 'message' => $message]);
    }

}
