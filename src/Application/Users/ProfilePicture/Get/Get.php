<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\Get;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Users\ProfilePictureAppService;

class Get extends ProfilePictureAppService {
    
    public function execute(BaseRequest $request): BaseResponse {    
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $picture = $this->findProfilePictureOrFail($request->pictureId);
        
        $dto = (new \App\DataTransformer\Users\PictureTransformer())->transform($picture);
        return new GetResponse($dto);
    }

}
