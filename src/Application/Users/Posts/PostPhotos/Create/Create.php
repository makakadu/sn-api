<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\PostPhotos\Create;

use App\Application\BaseResponse;
use App\Application\BaseRequest;
use App\Application\Users\PhotoAppService;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Common\PhotoService;
use App\Domain\Model\Users\Post\Photo\PhotoRepository;

class Create implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private PhotoService $photoService;
    private PhotoRepository $postPhotos;
    
    function __construct(UserRepository $users, PhotoService $photoService, PhotoRepository $postPhotos) {
        $this->users = $users;
        $this->photoService = $photoService;
        $this->postPhotos = $postPhotos;
    }

    
    public function execute(BaseRequest $request): BaseResponse {        
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $versions = $this->photoService->createPhotoVersionsFromUploaded($request->uploadedPhoto);
        $photo = $requester->createPostPhoto($versions);

        try {
            $this->postPhotos->add($photo);
            $this->postPhotos->flush();
        } catch (\Exception $ex) {
            $this->photoService->deleteFiles($versions);
            throw $ex;
        }
        return new CreateResponse($photo->id());
    }

}