<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\Delete;

use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;

class Delete extends PostAppService {
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);

        $post = $this->findPostOrFail($request->postId, true);
        if($request->property === 'deleted') {
            $post->delete($requester);
        } else {
            $post->deleteByGlobalModerator($requester);
        }
        return new DeleteResponse('post deleted');
    }
}
