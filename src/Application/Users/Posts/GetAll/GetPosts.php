<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetPosts;

use App\Application\BaseRequest;
use App\Application\Users\Posts\PostAppService;

class GetPosts extends PostAppService {
    
    public function execute(BaseRequest $request): GetPostsResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $owner = $this->findUserOrFail($request->ownerId, false, null);
        
        $requester ? $this->postsAuth->failIfCannotSeePosts($requester, $owner)
                   : $this->postsAuth->failIfGuestsCannotSeePosts($owner);
        
        $posts = $this->posts->getByOwnerId(
                $owner->id(),
                $request->offsetId,
                $request->count ? (int)$request->count : 50
        );
        return new GetPostsResponse($posts);
    }
}
