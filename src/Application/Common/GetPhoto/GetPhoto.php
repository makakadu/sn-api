<?php
declare(strict_types=1);
namespace App\Application\Users\GetPhoto;

use App\Application\ApplicationService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Authorization\UserPhotosAuth;
use App\Domain\Model\Authorization\GroupPhotosAuth;
use App\Domain\Model\Authorization\PagePhotosAuth;
use App\Domain\Model\Common\PhotoRepository;
use App\Application\CheckPhotoIsActiveVisitor;
use App\Domain\Model\Common\Comments\CommentRepository;

class GetPhoto implements ApplicationService {
    
    private PhotoRepository $photos;
    private CommentRepository $comments;
    private UserPhotosAuth $userPhotosAuth;
    private GroupPhotosAuth $groupPhotosAuth;
    private PagePhotosAuth $pagePhotosAuth;
            
    function findPhotoOrFail(string $photoId, bool $asTarget) {
        $found = true;
        
        $photo = $this->photos->getById($photoId);
        if(!$photo) {
            $found = false;
        } else {
            $found = $photo->accept(new CheckPhotoIsActiveVisitor($this->comments));
        }
        
        if(!$found && $asTarget) {
            throw new NotExistException("Photo $photoId not found");
        } elseif(!$found && !$asTarget) {
            throw new UnprocessableRequestException("Photo $photoId not found");
        }
    }

    public function execute(BaseRequest $request): BaseResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
  
        $photo = $this->findPhotoOrFail($request->photoId, true);
        
        if($requester) {
            if($photo instanceof UserPhoto) {
                $this->userPhotosAuth->failIfCannotSee($requester, $photo);
            } elseif($photo instanceof GroupPhoto) {
                $this->groupPhotosAuth->failIfCannotSee($requester, $photo);
            } elseif($photo instanceof PagePhoto) {
                $this->pagePhotosAuth->failIfCannotSee($requester, $photo);
            }
        } else {
            if($photo instanceof UserPhoto) {
                $this->userPhotosAuth->failIfGuestsCannotSee($requester, $photo);
            } elseif($photo instanceof GroupPhoto) {
                $this->groupPhotosAuth->failIfGuestsCannotSee($requester, $photo);
            } elseif($photo instanceof PagePhoto) {
                $this->pagePhotosAuth->failIfGuestsCannotSee($requester, $photo);
            }
        }
        
        return new GetPhotoResponse($photo);
    }
    
    public function execute_2(BaseRequest $request): BaseResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
  
        $photoId = $request->id;
        
        $photo = $this->userPhotos->getCommentPhotoById($photoId);
        if($photo) {
            $requester
                ? $this->userPhotosAuth->failIfCannotSeeCommentPhoto($requester, $photo)
                : $this->userPhotosAuth->failIfGuestsCannotSeeCommentPhoto($photo);
            goto return_response;
        }
        $photo = $this->userPhotos->getPostPhotoById($photoId);
        if($photo) {
            $requester
                ? $this->userPhotosAuth->failIfCannotSeePostPhoto($requester, $photo)
                : $this->userPhotosAuth->failIfGuestsCannotSeePostPhoto($photo);
            goto return_response;
        }
        $photo = $this->userPhotos->getAlbumPhotoById($photoId);
        if($photo) {
            $requester
                ? $this->userPhotosAuth->failIfCannotSeeAlbumPhoto($requester, $photo)
                : $this->userPhotosAuth->failIfGuestsCannotSeeAlbumPhoto($photo);
            goto return_response;
        }
        
        $photo = $this->userPhotos->getProfilePictureById($photoId);
        if($photo) {
            $requester
                ? $this->userPhotosAuth->failIfCannotSeeProfilePicture($requester, $photo)
                : $this->userPhotosAuth->failIfGuestsCannotSeeProfilePicture($photo);
            goto return_response;
        }
        
        
        return_response:
        return new GetPhotoResponse($photo);
    }
}