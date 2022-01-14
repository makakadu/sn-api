<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\Patch;

use App\Application\BaseRequest;
use App\Application\Users\Posts\PostAppService;

class Patch extends PostAppService {

    public function execute(BaseRequest $request): PatchResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $post = $this->findPostOrFail($request->postId, true);

        if($request->property === 'comments_are_disabled') {
            $request->value
                ? $post->disableComments($requester)
                : $post->enableComments($requester);
        }
        elseif($request->property === 'reactions_are_disabled') {
            $request->value
                ? $post->disableReactions($requester)
                : $post->enableReactions($requester);
        }
        elseif($request->property === 'is_public') {
            $post->changeIsPublic($requester, $request->value);
        }
        elseif($request->property === 'deleted') {
            $request->value
                ? $post->delete($requester)
                : $post->restore($requester);
        }
        elseif($request->property === 'deleted_by_global_moderation') {
            $request->value
                ? $post->deleteByGlobalModer($requester)
                : $post->restoreByGlobalModer($requester);
        }
        else {
            throw new \App\Application\Exceptions\UnprocessableRequestException(123, "Incorrect param name");
        }

        return new PatchResponse('OK');
    }

}
