<?php

declare(strict_types=1);

namespace App\Application\Exceptions;

class NotAuthenticatedException2 extends ApplicationException {
    private array $payload;

    function __construct(array $payload) {
        $this->payload = $payload;
    }
    
    function payload(): array {
        return $this->payload;
    }
}
