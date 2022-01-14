<?php
declare(strict_types=1);
namespace App\Application\Users\Settings\UpdatePrivacy;

use App\Application\BaseRequest;

class UpdatePrivacyRequest implements BaseRequest {
    public string $requesterId;
    /** @var mixed $payload */
    public $payload;
    
    /** @param mixed $payload */
    public function __construct(string $requesterId, $payload) {
        $this->requesterId = $requesterId;
        $this->payload = $payload;
    }
}
