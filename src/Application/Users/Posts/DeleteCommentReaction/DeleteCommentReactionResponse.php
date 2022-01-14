<?php //
declare(strict_types=1);
namespace App\Application\Users\Posts\DeleteCommentReaction;

use App\Application\BaseResponse;

class DeleteCommentReactionResponse implements BaseResponse {
    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
