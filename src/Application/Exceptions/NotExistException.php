<?php

declare(strict_types=1);

namespace App\Application\Exceptions;

use App\Application\Exceptions\ApplicationException;
use App\Application\Errors;

class NotExistException extends ApplicationException {
    public int $statusCode = 404;
            
    function __construct(string $message) {
        $errorInfo = ['message' => $message, 'code' => Errors::RESOURCE_NOT_FOUND['code']];
        parent::__construct($errorInfo, null);
    }
}
