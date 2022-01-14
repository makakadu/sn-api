<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\UpdateCommentReaction;

use App\Application\BaseResponse;

class UpdateCommentReactionResponse implements BaseResponse {

    public string $responseMessage;

    public function __construct(string $responseMessage) {
        $this->responseMessage = $responseMessage;
    }
}
