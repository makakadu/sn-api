<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\Create;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Common\Shares\SharesService;
//use App\Domain\Model\Users\AttachmentsService;
use App\Validation\Users\PostDataValidator;
use App\Domain\Model\Users\Post\AttachmentRepository as PostAttachmentRepository;

class Create implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    public function __construct(UserRepository $users, SharesService $sharesService, PostAttachmentRepository $attachments) {
        $this->users = $users;
        $this->sharesService = $sharesService;
        $this->attachments = $attachments;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $this->validateRequest($request);
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $shared = $request->shared
            ? $this->sharesService->prepareShared($requester, $request->shared['type'], $request->shared['id'])
            : null;
        
        $post = $requester->createPost(
            $request->text,
            (bool)$request->disableComments,
            (bool)$request->disableReactions,
            (bool)$request->isPublic,
            $shared,
            $this->attachments->getByIds($request->attachments)
            //$this->attachmentsService->prepareAttachmentsForUserPost($requester, $request->attachments),
        );
        //exit;
        return new CreateResponse($post->id());
    }
    
    private function validateRequest(CreateRequest $request): void {     
        PostDataValidator::validateParamText($request->text);
        PostDataValidator::validateParamDisableComments($request->disableComments);
        PostDataValidator::validateParamDisableReactions($request->disableReactions);
        PostDataValidator::validateParamIsPublic($request->isPublic);
        PostDataValidator::validateParamAttachments($request->attachments);
        if($request->shared) {
            PostDataValidator::validateParamShared($request->shared);
        }
    }
}