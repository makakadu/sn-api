<?php
declare(strict_types=1);
namespace App\Application\Users\Photos\Update;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Users\Photos\PhotoAppService;
use Assert\Assertion;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Exceptions\ValidationException;

class Update extends PhotoAppService {
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);

        $photo = $this->findPhotoOrFail($request->photoId, true);
        
        $commentId = $photo->commentId();
        
        $comment = null;
        
        $comment = $this->postComments->getById($commentId);
        if($comment) {
            $this->failIfEditTimeExpired($comment);
            $this->postsAuth->failIfCannotEditComment($requester, $comment);
            goto kek;
        }
        $comment = $this->photoComments->getById($commentId);
        if($comment) {
            $this->failIfEditTimeExpired($comment);
            // Ещё одна причина не делать всю эту чепуху - есть только один класс Comment для всех ProfilePicture и AlbumPhoto
            $this->pAuth->failIfCannotEditComment($requester, $comment);
            goto kek;
        }
        $comment = $this->videoComments->getById($commentId);
        if($comment) {
            $this->failIfEditTimeExpired($comment);
            goto kek;
        }
        
        kek:
        foreach ($request->payload as $key => $value) {
            if($key === 'is_deleted') {
                if(count($request->payload) > 1) {
                    $message = "If property 'is_deleted' is updating then other properies cannot be updated in same request";
                    throw new UnprocessableRequestException(123, $message);
                }
                $this->validateParamIsDeleted($value);
                $value ? $photo->delete() : $photo->restore();
            }
            elseif($key === 'is_deleted_by_manager') {
                if(count($request->payload) > 1) {
                    $message = "If property 'is_deleted_by_manager' is updating then other properies cannot be updated in same request";
                    throw new UnprocessableRequestException(123, $message);
                }
                $this->photosAuth->failIfCannotUpdateAsManager($requester, $photo);
                $this->validateParamIsDeletedByManager($value);
                $value ? $photo->delete(true) : $photo->restore(true);
            }
        }
        
        return new UpdateResponse('Photo changed successfully');
    }
    
    /** @param mixed $isDeleted */
    function validateParamIsDeleted($isDeleted): void {
        try {
            Assertion::boolean($isDeleted, "Param 'is_deleted' should be a boolean");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new ValidationException($ex->getMessage());
        }
    }
    
    /** @param mixed $isDeletedByManager */
    function validateParamIsDeletedByManager($isDeletedByManager): void {
        try {
            Assertion::boolean($isDeletedByManager, "Param 'is_deleted_by_manager' should be a boolean");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new ValidationException($ex->getMessage());
        }
    }
    
    function failIfEditTimeExpired(ProfileComment $comment) {
        if($comment->createdAt()) {
            throw new \App\Domain\Model\DomainException("Editing time was expired");
        }
    }
}