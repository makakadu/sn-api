<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\DeleteReaction;

use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\ReactionRepository;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Application\Exceptions\NotExistException;

class DeleteReaction implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Posts\PostAppServiceTrait;
    
    private UserPostsAuth $postsAuth;
    
    function __construct(
        PostRepository $posts,
        UserRepository $users,
        UserPostsAuth $postsAuth
    ) {
        $this->users = $users;
        $this->posts = $posts;
        $this->postsAuth = $postsAuth;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $post = $this->findPostOrFail($request->postId, true);
        $reaction = $post->reactions()->get($request->reactionId);
        if(!$reaction) {
            throw new \App\Application\Exceptions\NotExistException('Reaction not found');
        }
        $this->postsAuth->failIfCannotDeleteReaction($requester, $reaction);
        
        $post->deleteReaction($reaction->id());

        return new DeleteReactionResponse('OK');
    }

}
