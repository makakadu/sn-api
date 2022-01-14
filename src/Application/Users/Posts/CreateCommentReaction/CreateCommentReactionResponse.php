<?php //
declare(strict_types=1);
namespace App\Application\Users\Posts\CreateCommentReaction;

use App\Application\BaseResponse;

class CreateCommentReactionResponse implements BaseResponse {
    public string $id;
    
    function __construct(string $id) {
        $this->id = $id;
    }
}
