<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\PostPhotos\Update;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Users\PhotoAppService;
use Assert\Assertion;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Exceptions\ValidationException;

class Update extends PhotoAppService {
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);

        $photo = $this->findPhotoOrFail($request->photoId, true);
        $this->photosAuth->failIfCannotUpdate($requester, $photo);
        
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
            elseif($key === 'description') {
                $this->validateParamDescription($value);
                $photo->changeDescription($value);
            } elseif($key === 'album_id') {
                $this->validateParamAlbumId($value);
                $requester->addPhotoToAlbum($value, $photo);
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
}