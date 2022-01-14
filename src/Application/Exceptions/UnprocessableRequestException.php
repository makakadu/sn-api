<?php

declare(strict_types=1);

namespace App\Application\Exceptions;

use App\Application\Exceptions\ApplicationException;

class UnprocessableRequestException extends ApplicationException {
    public int $statusCode = 422;
    private int $errorNumber;
    
    function __construct(int $errorNumber, string $message) {
        $this->code = $errorNumber;
        $this->errorNumber = $errorNumber;
        $this->message = $message;
    }
    
    function getErrorNumber(): int {
        return $this->errorNumber;
    }
}