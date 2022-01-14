<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\GetComments;

use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;

class GetComments extends PostAppService {
    
    public function execute(BaseRequest $request): BaseResponse {               
//        $requester = $request->requesterId ? $this->findRequesterOrFail($request->requesterId) : null;
//        $post = $this->findPostOrFail($request->postId, false);
//        $this->postsAuthorization->failIfAccessToPostProhibited($requester, $post);

        $comments = [];//$this->comments->getPart($post->id(), $request->offsetCommentId, $request->limit);

        return new GetCommentsResponse($comments);
    }
}
