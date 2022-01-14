<?php
declare(strict_types=1);
namespace App\Domain\Model;

use App\Application\Exceptions\ApplicationException;

class DomainException extends ApplicationException {
    protected int $statusCode = 422;

    function __construct(string $message) {
        parent::__construct(['code' => 222, 'message' => $message]);
    }
}
