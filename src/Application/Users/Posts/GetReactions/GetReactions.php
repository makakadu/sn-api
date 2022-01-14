<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetReactions;

use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Post\ReactionRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\DataTransformer\Users\PostTransformer;

class GetReactions implements \App\Application\ApplicationService {
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
        
        $post = $this->findPostOrFail($request->postId, false);
        
        if($requester) {
            $this->postsAuth->failIfCannotSee($requester, $post);
        } else {
            $this->postsAuth->failIfGuestsCannotSee($post);
        }

        $criteria = \Doctrine\Common\Collections\Criteria::create()->setMaxResults(10);
        $reactions = $post->reactions()->matching($criteria);

        $dtos = [];
        foreach ($reactions as $reaction) {
            $dtos[] = $this->postTransformer->reactionToDTO($reaction);
        }
        return new GetReactionsResponse($dtos, $this->postTransformer->prepareReactionsCount($post->reactions()));
    }
}

