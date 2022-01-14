<?php
declare(strict_types=1);
namespace App\Application\Common\TempPhoto\Create;

use App\Application\BaseResponse;
use App\Application\BaseRequest;
use App\Application\Users\Photos\PhotoAppService;
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
        $requester->createTempPhoto($versions);
        
        return new CreateResponse('OK');
    }

}