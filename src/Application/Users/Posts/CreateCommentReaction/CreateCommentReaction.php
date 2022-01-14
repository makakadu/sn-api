<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\CreateCommentReaction;

use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Users\Post\Comment\CommentRepository as PostCommentRepository;

class CreateCommentReaction implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Posts\PostCommentAppService;

    function __construct(
        UserRepository $users,
        UserPostsAuth $postsAuth,
        PostCommentRepository $comments
    ) {
        $this->users = $users;
        $this->postsAuth = $postsAuth;
        $this->comments = $comments;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $comment = $this->findCommentOrFail($request->commentId, true);
        $this->postsAuth->failIfCannotSee($requester, $comment->commentedPost());

        $reaction = $comment->react($requester, $request->type); // Если пользователь видит пост, то может оставить реакцию на любой коммент
        return new CreateCommentReactionResponse($reaction->id());
    }
}
