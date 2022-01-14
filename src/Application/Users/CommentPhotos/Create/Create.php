<?php
declare(strict_types=1);
namespace App\Application\Users\CommentPhotos\Create;

use App\Application\BaseResponse;
use App\Application\BaseRequest;
use App\Application\Users\Photos\PhotoAppService;
use App\Domain\Model\Users\Comments\Photo\PhotoRepository;
use App\Domain\Model\Common\PhotoService;
use App\Domain\Model\Users\User\UserRepository;

class Create implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private PhotoRepository $photos;
    private PhotoService $photoService;
    
    function __construct(PhotoRepository $photos, PhotoService $photoService, UserRepository $users) {
        $this->photos = $photos;
        $this->photoService = $photoService;
        $this->users = $users;
    }

    
    public function execute(BaseRequest $request): BaseResponse {        
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $versions = $this->photoService->createPhotoVersionsFromUploaded($request->uploadedPhoto);
        $photo = $requester->createCommentPhoto($versions);
        try {
            $this->photos->add($photo);
            $this->photos->flush();
        } catch (\Exception $ex) {
            $this->photoService->deleteFiles($versions);
            throw $ex;
        }
        return new CreateResponse($photo->id());
    }

}
