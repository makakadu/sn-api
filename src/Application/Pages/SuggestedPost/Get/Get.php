<?php
declare(strict_types=1);
namespace App\Application\Pages\SuggestedPost\Get;

use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Post\SuggestedPost\SuggestedPostRepository;

class Get extends PostAppService {
    use AppServiceTrait;
    use SuggestedPostAppServiceTrait;
    
    private SuggestedPostTransformer $postTransformer;
    private SuggestedPostRepository $posts;
    private UserRepository $users;
    
    public function __construct(SuggestedPostTransformer $postTransformer, SuggestedPostRepository $posts, UserRepository $users) {
        $this->postTransformer = $postTransformer;
        $this->posts = $posts;
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        $post = $this->findSuggestedPostOrFail($request->postId, true);
        
        if(!$post->canSee($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Access to suggested post is forbidden");
        }
        return new GetResponse($this->postTransformer->transformer($post));
    }
}
