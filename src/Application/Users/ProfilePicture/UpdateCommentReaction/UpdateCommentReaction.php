<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\UpdateCommentReaction;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Users\Photos\PhotoCommentAppService;

class UpdateCommentReaction extends PhotoCommentAppService {
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $this->validateParamReactionType($request->type);

        $comment = $this->findCommentOrFail($request->commentId, false);
        $reaction = $comment->getReaction($request->reactionId);
        $this->photosAuth->failIfCannotUpdateCommentReaction($requester, $reaction);
        
        $reaction->changeType($request->type);
        return new UpdateCommentReactionResponse('OK');
    }
    
    
}