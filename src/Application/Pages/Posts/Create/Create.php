<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\Create;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Common\Shares\SharesService;
use App\Domain\Model\Users\AttachmentsService;
use App\Domain\Model\Pages\Page\PageRepository;
use App\Domain\Model\Pages\Post\SuggestedPost\SuggestedPostRepository;
use App\Application\Pages\Posts\PostParamsValidator;

class Create implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\PageAppService;
    use \App\Application\Pages\SuggestedPost\SuggestedPostAppService;
    
    private SharesService $sharesService;
    private AttachmentsService $attachmentsService;
    
    public function __construct(
        UserRepository $users, 
        PageRepository $pages, 
        SharesService $sharesService, 
        AttachmentsService $attachmentsService, 
        SuggestedPostRepository $suggestedPosts
    ) {
        $this->users = $users;
        $this->pages = $pages;
        $this->sharesService = $sharesService;
        $this->attachmentsService = $attachmentsService;
        $this->suggestedPosts = $suggestedPosts;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $this->validateRequest($request);
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $page = $this->findPageOrFail($request->pageId, false, null);
        
        if($request->suggestedId) {
            $suggested = $this->findSuggestedPostOrFail($request->suggestedId, false, null);
            
            $page->createPostFromSuggested(
                $suggested,
                $requester,
                $request->text,
                (bool)$request->disableComments,
                (bool)$request->disableReactions,
                $this->attachmentsService->prepareAttachmentsForPagePost($requester, $request->attachments)
            );
            
            $this->suggestedPosts->remove($suggested);
        } else {
            $shared = !$request->shared ? null
                : $this->sharesService->prepareShared($requester, $request->shared['type'], $request->shared['id']);
            
            $page->createPost(
                $requester,
                $request->text,
                (bool)$request->disableComments,
                (bool)$request->disableReactions,
                $shared,
                $this->attachmentsService->prepareAttachmentsForPagePost($requester, $request->attachments),
                (bool)$request->addSignature ?? false
            );
        }
        return new CreateResponse("OK");
    }
    
    private function validateRequest(CreateRequest $request): void {     
        PostParamsValidator::validateParamText($request->text);
        PostParamsValidator::validateParamDisableComments($request->disableComments);
        PostParamsValidator::validateParamDisableReactions($request->disableReactions);
        if(!is_null($request->shared)) {
            PostParamsValidator::validateParamShared($request->shared);
        }
        PostParamsValidator::validateParamAttachments($request->attachments);
        if(!is_null($request->addSignature)) {
            PostParamsValidator::validateParamAddSignature($request->addSignature);
        }
        if(!is_null($request->suggestedId)) {
            \Assert\Assertion::string($request->suggestedId, "Param 'from_suggesterd' should be string");
        }
    }
}