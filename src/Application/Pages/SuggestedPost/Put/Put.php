<?php
declare(strict_types=1);
namespace App\Application\Pages\SuggestedPost\Put;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\AttachmentsService;
use App\Domain\Model\Pages\Post\SuggestedPost\SuggestedPostRepository;
use \App\Application\Pages\SuggestedPost\SuggestedPostAppService;
use App\Application\Pages\Posts\PostAppServiceTrait;
use \App\Application\AppServiceTrait;
use App\Application\Pages\Posts\PostParamsValidator;

class Put implements \App\Application\ApplicationService { 
    use AppServiceTrait;
    //use PostAppServiceTrait;
    use SuggestedPostAppService;

    public function __construct(SuggestedPostRepository $posts, UserRepository $users, AttachmentsService $attachmentsService) {
        $this->posts = $posts;
        $this->users = $users;
        $this->attachmentsService = $attachmentsService;
    }

    public function execute(BaseRequest $request): PutResponse {
        $this->validateRequest($request);
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $post = $this->findSuggestedPostOrFail($request->suggestedPostId, true);
        
        $attachments = $this->attachmentsService->prepareAttachmentForPagePost($requester, $request->attachments);
        $post->edit($request->text, $attachments, $request->hideSignatureIfEdited);

        return new PutResponse('OK');
    }
    
    private function validateRequest(CreateRequest $request): void {
        PostParamsValidator::validateParamHideSignatureIfEdited($request->hideSignatureIfEdited);
        PostParamsValidator::validateParamText($request->text);
        PostParamsValidator::validateParamAttachments($request->attachments);
    }
}
