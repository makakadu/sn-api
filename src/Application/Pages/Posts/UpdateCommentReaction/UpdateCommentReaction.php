<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\UpdateCommentReaction;

use App\Application\Users\Posts\PostCommentReactionAppService;
use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Post\Comment\CommentRepository as PostCommentRepository;

class UpdateCommentReaction implements \App\Application\ApplicationService {
    use \App\Application\Pages\Posts\PostCommentAppServiceTrait;
    use \App\Application\AppServiceTrait;
    
    public function __construct(PostCommentRepository $comments, UserRepository $users) {
        $this->comments = $comments;
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        //$this->validateParamReactionType($request->reactionType);
        
        $comment = $this->findCommentOrFail($request->commentId, false);
        $comment->editReaction($requester, $request->reactionId, $request->type);
        
        return new UpdateCommentReactionResponse('OK');
    }

}
