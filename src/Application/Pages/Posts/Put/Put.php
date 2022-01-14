<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\Put;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\AttachmentsService;
use App\Domain\Model\Pages\Post\SuggestedPost\SuggestedPostRepository;
use App\Application\Pages\Posts\PostParamsValidator;

class Put implements \App\Application\ApplicationService { 
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\Posts\PostAppServiceTrait;

    public function __construct(SuggestedPostRepository $posts, UserRepository $users, AttachmentsService $attachmentsService) {
        $this->posts = $posts;
        $this->users = $users;
        $this->attachmentsService = $attachmentsService;
    }

    public function execute(BaseRequest $request): PutResponse {
        $this->validateRequest($request);
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $post = $this->findPostOrFail($request->suggestedPostId, true);
        
        $attachments = $this->attachmentsService->prepareAttachmentForPagePost($requester, $request->attachments);
        $post->edit($request->text, $attachments, $request->hideSignatureIfEdited);

        return new PutResponse('OK');
    }
    
    private function validateRequest(CreateRequest $request): void {     
        PostParamsValidator::validateParamText($request->text);
        PostParamsValidator::validateParamDisableComments($request->disableComments);
        PostParamsValidator::validateParamDisableReactions($request->disableReactions);
        PostParamsValidator::validateParamAttachments($request->attachments);
        PostParamsValidator::validateParamAddSignature($request->addSignature);
    }
}
