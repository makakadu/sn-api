<?php
declare(strict_types=1);
namespace App\Application\Users\Cover\Get;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Users\ProfilePictureAppService;

class Get extends \App\Application\Users\Cover\CoverAppService {
    
    public function execute(BaseRequest $request): BaseResponse {    
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $cover = $this->findProfileCoverOrFail($request->pictureId);
        
        $dto = (new \App\DataTransformer\Users\CoverTransformer())->transform($cover);
        return new GetResponse($dto);
    }

}
