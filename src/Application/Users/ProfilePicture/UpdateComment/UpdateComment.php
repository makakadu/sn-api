<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\UpdateComment;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Users\Photos\PhotoCommentAppService;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Exceptions\MalformedRequestException;

class UpdateComment extends PhotoCommentAppService {
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $comment = $this->findCommentOrFail($request->commentId);
        $this->profilePictureAuth->failIfCannotEditComment($requester, $comment);
        
        foreach ($request->payload as $key => $value) {
            if($key === 'is_deleted') {
                if(count($request->payload) > 1) {
                    throw new UnprocessableRequestException(123, "If property 'is_deleted' is updating then other properies cannot be updated in same request");
                }
                $this->validateParamIsDeleted($value);
                $value ? $comment->delete(false) : $comment->restore();
            }
            elseif($key === 'text') {
                $this->validateParamText($value);
                $comment->changeText($value);
            }
            elseif($key === 'attachment') {
                $this->validateParamAttachment($value);
                $type = $value['type'];
                $id = $value['id'];

                $this->attachmentsService->removeOldAttachmentFromProfileCommentIfNeed($comment, $id);
                
                if($type === 'photo') {
                    $photo = $this->attachmentsService->preparePhotoForProfileComment($requester, $comment, $id);
                    $comment->addPhoto($photo);
                }
                elseif($type === 'video') {
                    $video = $this->attachmentsService->prepareVideoForProfileComment($requester, $comment, $id);
                    $comment->addVideo($video);
                } else {
                    throw new MalformedRequestException("Incorrect attachment type");
                }
            }
        }
        return new UpdateCommentResponse('OK');
    }
}
