<?php
declare(strict_types=1);
namespace App\Application\Users\Ban\Patch;

use App\Application\BaseRequest;
use App\Application\Users\Posts\PostAppService;

class Patch implements \App\Application\ApplicationService {
    
    public function execute(BaseRequest $request): PatchResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $ban = $this->findBanOrFail($request->banId, true);

        if($request->property === 'canceled') {
            $ban->cancel();
        }
        elseif($request->property === 'minutes') {
            $ban->changeDuration($request->value);
        }
        else {
            throw new \App\Application\Exceptions\UnprocessableRequestException(123, "Incorrect param name");
        }

        return new PatchResponse('OK');
    }
    
//    private function validateRequest(CreatePostRequest $request): void {     
//        $this->validateParamText($request->text);
//        $this->validateParamAttachments($request->attachments);
//        $this->validateParamDisableComments($request->disableComments);
//        $this->validateParamPublic($request->public);
//    }
}
