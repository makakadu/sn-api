<?php
declare(strict_types=1);
namespace App\Application\Groups\UpdatePost;

class UpdatePostResponse {

    public $responseMessage;

    public function __construct(string $responseMessage) {
        $this->responseMessage = $responseMessage;
    }
}
