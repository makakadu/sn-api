<?php
declare(strict_types=1);
namespace App\Application\Groups\UpdatePost;

use App\Application\Groups\PostApplicationService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;

class UpdatePost extends PostApplicationService {
    const TEXT = 'text';
    const MEDIA = 'media';
    const ARE_COMMENTS_DISABLED = 'areCommentsDisabled';
        
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $this->validateRequestData($request->payload);

        $post = $this->findPostOrFail($request->postId, true);
        $this->postsAuth->failIfCannotUpdateComment($requester, $post);
        
        $postAttachments = [];
        foreach ($request->attachments as $attachment) {
            $type = $attachment['type'];
            $id = $attachment['id'];
            
            if($type === 'group-photo' || $type === 'user-photo') {
                $postAttachments[] = $this->attachmentsService->prepareGroupPhotoForNewPost($requester, $post->group(), $type, $id);
            } 
            elseif($type === 'group-video' || $type === 'user-video') {
                $postAttachments[] = $this->attachmentsService->prepareGroupVideoForNewPost($requester, $post->group(), $type, $id);
            }
        }
        return new UpdatePostResponse('Post updated');
    }
}
