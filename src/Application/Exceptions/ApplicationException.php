<?php

declare(strict_types=1);

namespace App\Application\Exceptions;

abstract class ApplicationException extends \RuntimeException {
    private ?string $detailedReason;
    protected int $statusCode;
    
    /**
    * @param array<mixed> $errorInfo
    **/   
    function __construct(array $errorInfo, ?string $detailedReason = null) {
        parent::__construct($errorInfo['message'], $errorInfo['code'], null);
        $this->detailedReason = $detailedReason;
    }
    
    public function detailedReason(): ?string {
        return $this->detailedReason;
    }
    
    public function statusCode(): int {
        return $this->statusCode;
    }
}
