<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\CreateCommentReaction;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Post\Comment\CommentRepository as PostCommentRepository;

class CreateCommentReaction implements \App\Application\ApplicationService {
    use \App\Application\Pages\Posts\PostCommentAppServiceTrait;
    use \App\Application\AppServiceTrait;
    
    public function __construct(PostCommentRepository $comments, UserRepository $users) {
        $this->comments = $comments;
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $comment = $this->findCommentOrFail($request->commentId, true);
        $comment->react($requester, $request->type, (bool)$request->onBehalfOfPage);

        return new CreateCommentReactionResponse('ok');
    }
}
