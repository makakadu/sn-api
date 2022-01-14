<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetCommentReaction;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\Comment\CommentRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\DataTransformer\Users\PostTransformer;

class GetCommentReaction implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Posts\PostAppServiceTrait;
    use \App\Application\Users\Posts\PostCommentAppService;
    
    private UserPostsAuth $postsAuth;
    private PostTransformer $postTransformer;
    
    function __construct(
        CommentRepository $comments,
        UserRepository $users,
        UserPostsAuth $postsAuth,
        PostTransformer $postTransformer
    ) {
        $this->users = $users;
        $this->comments = $comments;
        $this->postsAuth = $postsAuth;
        $this->postTransformer = $postTransformer;
    }
    
    public function execute(BaseRequest $request): BaseResponse {               
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $comment = $this->findCommentOrFail($request->commentId, true);
        $reaction = $comment->reactions()->get($request->reactionId);
        if(!$reaction) {
            throw new \App\Application\Exceptions\NotExistException('Reaction not found');
        }
        if($requester) {
            //$this->postsAuth->failIfCannotSeeReaction($requester, $reaction);
        } else {
            //$this->postsAuth->failIfGuestsCannotSeeReaction($reaction);
        }

        return new GetCommentReactionResponse($this->postTransformer->reactionToDTO($reaction));
    }
}

