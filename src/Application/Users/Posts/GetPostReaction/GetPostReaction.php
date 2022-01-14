<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetPostReaction;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\DataTransformer\Users\PostTransformer;

class GetPostReaction implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Posts\PostAppServiceTrait;
    
    private UserPostsAuth $postsAuth;
    private PostTransformer $postTransformer;
    
    function __construct(
        PostRepository $posts,
        UserRepository $users,
        UserPostsAuth $postsAuth,
        PostTransformer $postTransformer
    ) {
        $this->users = $users;
        $this->posts = $posts;
        $this->postsAuth = $postsAuth;
        $this->postTransformer = $postTransformer;
    }
    
    public function execute(BaseRequest $request): BaseResponse {               
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $post = $this->findPostOrFail($request->postId, true);
        $reaction = $post->reactions()->get($request->reactionId);
        if(!$reaction) {
            throw new \App\Application\Exceptions\NotExistException('Reaction not found');
        }
        if($requester) {
            //$this->postsAuth->failIfCannotSeeReaction($requester, $reaction);
        } else {
            //$this->postsAuth->failIfGuestsCannotSeeReaction($reaction);
        }

        return new GetPostReactionResponse($this->postTransformer->reactionToDTO($reaction));
    }
}

