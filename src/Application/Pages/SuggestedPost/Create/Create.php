<?php
declare(strict_types=1);
namespace App\Application\Pages\SuggestedPost\Create;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Page\PageRepository;
use App\Domain\Model\Users\AttachmentsService;
use App\Application\Pages\Posts\PostParamsValidator;

class Create implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\PageAppService;

    private AttachmentsService $attachmentsService;
    
    function __construct(
        UserRepository $users,
        PageRepository $pages,
        AttachmentsService $attachmentsService
    ) {
        $this->users = $users;
        $this->pages = $pages;
        $this->attachmentsService = $attachmentsService;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        //$this->validateRequest($request);
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $page = $this->findPageOrFail($request->pageId, false, null);

        $page->createSuggestedPost(
            $requester,
            $request->text,
            $this->attachmentsService->prepareAttachmentsForPagePost($requester, $request->attachments),
            (bool)$request->addSignature,
            (bool)$request->hideSignatureIfEdited
        );
        //exit;
        return new CreateResponse("OK");
    }
    
    private function validateRequest(CreateRequest $request): void {
        PostParamsValidator::validateParamHideSignatureIfEdited($request->hideSignatureIfEdited);
        PostParamsValidator::validateParamText($request->text);
        PostParamsValidator::validateParamAttachments($request->attachments);
    }
}