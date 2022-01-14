<?php
declare(strict_types=1);
namespace App\Domain\Model;

use App\Application\Exceptions\ApplicationException;

class DomainExceptionAlt extends ApplicationException {
    private array $payload;

    function __construct(array $payload) {
        $this->payload = $payload;
    }
    
    function payload(): array {
        return $this->payload;
    }
}
