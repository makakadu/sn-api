<?php
declare(strict_types=1);

namespace App\Application\Groups\GetPost;

use App\Application\ApplicationService;
use App\Application\Exceptions\NotExistException;
use App\Application\BaseRequest;
use App\Application\BaseResponse;

class GetPost implements ApplicationService {

    public function execute(BaseRequest $request): BaseResponse{
        //$requester = $this->findAuthenticatedUser($this->getCurrentUserId());
        $post = null;//$this->groupPosts->getById($request->postId);
        if(!$post) { throw new NotExistException('Post not found'); }

        return new GetPostResponse($post);
    }
}
