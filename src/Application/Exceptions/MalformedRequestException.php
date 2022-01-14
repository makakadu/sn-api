<?php

declare(strict_types=1);

namespace App\Application\Exceptions;

use App\Application\Exceptions\ApplicationException;

class MalformedRequestException extends ApplicationException {
    protected int $statusCode = 400;

    function __construct(string $message) {
        parent::__construct(['code' => 666, 'message' => $message]);
    }

}
