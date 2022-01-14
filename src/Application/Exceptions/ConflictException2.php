<?php
declare(strict_types=1);
namespace App\Application\Exceptions;

use App\Application\Exceptions\ApplicationException;

class ConflictException2 extends \RuntimeException {
    private array $payload;

    function __construct(array $payload) {
        $this->payload = $payload;
    }
    
    function payload(): array {
        return $this->payload;
    }
}
