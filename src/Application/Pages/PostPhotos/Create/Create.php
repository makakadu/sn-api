<?php
declare(strict_types=1);
namespace App\Application\Pages\PostPhotos\Create;

use App\Application\BaseResponse;
use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Common\PhotoService;


class Create implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private PhotoService $photoService;
    
    function __construct(UserRepository $users, PhotoService $photoService) {
        $this->users = $users;
        $this->photoService = $photoService;
    }
    
    public function execute(BaseRequest $request): BaseResponse {        
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $versions = $this->photoService->createPhotoVersionsFromUploaded($request->uploadedPhoto);
        $photo = $requester->createPostPhoto($versions);
        $this->postPhotos->add($photo);
        try {
            $this->postPhotos->flush();
        } catch (\Exception $ex) {
            $this->photoService->deleteFiles($versions);
            throw $ex;
        }
        return new CreateResponse("Ok");
    }

}