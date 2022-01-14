<?php
declare(strict_types=1);
namespace App\Application\Groups\CreateVideo;

use App\Application\BaseRequest;
use App\Application\BaseResponse;

class CreateVideo {

    public function execute(BaseRequest $request) : CreateVideoResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $group = $this->findGroupOrFail($request->groupId, false);
        
//        if($request->asGroup) {
//            $this->videoAuthorization->failIfCannotCreateAsGroup($requester, $group);
//        } else {
            $this->videoAuthorization->failIfCannotCreate($requester, $group);
//        }

        $asGroup = (bool)$this->memberships->getByUserAndGroup($requester, $group);
            
        $src = $this->videoService->getSrc();
        $previews = $this->photoService->createPreviews();
        $video = $group->createVideo($requester, $src, $previews, $asGroup);
        
        return new CreateVideoResponse($video);
    }
}
