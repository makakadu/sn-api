<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\UpdateReaction;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Users\Posts\PostAppService;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Users\Post\ReactionRepository;

class UpdateReaction implements \App\Application\ApplicationService {
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
        $this->postsAuth->failIfCannotEditReaction($requester, $reaction);

        $reaction->edit($request->type);
        return new UpdateReactionResponse('OK');
    }

}
