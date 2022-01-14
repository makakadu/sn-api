<?php
declare(strict_types=1);
namespace App\Application\Users\CommentPhotos\CreateFromPhoto;

use App\Application\BaseResponse;
use App\Application\BaseRequest;
use App\Application\Users\Photos\PhotoAppService;
use App\Domain\Model\Users\Comments\Photo\PhotoRepository;
use App\Domain\Model\Common\PhotoService;
use App\Domain\Model\Users\User\UserRepository;
use App\Application\Errors;
use App\Domain\Model\Authorization\UserAlbumPhotosAuth;

class CreateFromPhoto implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private AlbumPhotoRepository $albumPhotos;
    
    function __construct(PhotoRepository $albumPhotos, UserRepository $users) {
        $this->albumPhotos = $albumPhotos;
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): BaseResponse {        
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $photo = $this->albumPhotos->getById($request->photoId);
        if(!$photo) {
            throw new NotFoundException("Photo not found");
        }
        if(!$photo->owner()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot create photo for comment from album photo of another user");
        }
    }

}