<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\Create;

use App\Application\BaseResponse;

class CreateResponse implements BaseResponse {

    public string $id;

    public function __construct(string $id) {
        $this->id = $id;
    }
}
