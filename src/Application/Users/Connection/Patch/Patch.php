<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\Patch;

use App\Application\BaseRequest;

class Patch implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;

    public function execute(BaseRequest $request): PatchResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $connection = $this->findConnectionOrFail($request->connectionId, true);

        if($request->property === 'is_deleted') {
            $connection->delete($requester);
        }
        elseif($request->property === 'is_accepted') {
            $connection->accept($requester);
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
