<?php

declare(strict_types=1);

namespace App\Application\Exceptions;

use App\Application\Exceptions\ApplicationException;

class ForbiddenException extends ApplicationException {
    protected int $statusCode = 403;
    private int $errorNumber;
    
    function __construct(int $errorNumber, string $message) {
        $this->errorNumber = $errorNumber;
        $this->message = $message;
    }
    
    function getErrorNumber(): int {
        return $this->errorNumber;
    }

}
