<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\Create;

use App\Application\BaseResponse;

class CreateResponse implements BaseResponse {

    public string $id;
    public ?string $subscriptionId;

    public function __construct(string $id, ?string $subscriptionId) {
        $this->id = $id;
        $this->subscriptionId = $subscriptionId;
    }
}
