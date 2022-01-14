<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\UpdateCommentReaction;

use App\Application\Users\Posts\PostCommentReactionAppService;
use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Users\Post\Comments\ReactionRepository;
use App\Domain\Model\Users\Post\Comment\CommentRepository;

class UpdateCommentReaction  implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Posts\PostAppServiceTrait;
    use \App\Application\Users\Posts\PostCommentAppService;
    
    private UserPostsAuth $postsAuth;
    
    function __construct(
        CommentRepository $comments,
        UserRepository $users,
        UserPostsAuth $postsAuth
    ) {
        $this->users = $users;
        $this->comments = $comments;
        $this->postsAuth = $postsAuth;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $comment = $this->findCommentOrFail($request->commentId, true);
        $reaction = $comment->reactions()->get($request->reactionId);
        if(!$reaction) {
            throw new \App\Application\Exceptions\NotExistException('Reaction not found');
        }
        //$this->postsAuth->failIfCannotEditReaction($requester, $reaction);

        $reaction->changeReactionType($request->type);
        return new UpdateCommentReactionResponse('OK');
    }

}
