<?php
declare(strict_types=1);
namespace App\Application\Groups\Posts\Create;

use App\Application\Groups\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Groups\Post\PostRepository;
use App\Domain\Model\Common\Shares\SharesService;
use App\Domain\Model\Common\AttachmentsService;
use App\Domain\Model\Groups\Group\GroupRepository;
use App\Domain\Model\Authorization\GroupPostsAuth;

class Create extends PostAppService {
    
    private SharesService $sharesService;
    private AttachmentsService $attachmentsService;
    
    function __construct(
        UserRepository $users,
        PostRepository $posts,
        GroupRepository $groups,
        GroupPostsAuth $postsAuth,
        SharesService $sharesService,
        AttachmentsService $attachmentsService
    ) {
        parent::__construct($posts, $users, $postsAuth, $groups);
        $this->sharesService = $sharesService;
        $this->attachmentsService = $attachmentsService;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        //$this->validateRequest($request);
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $group = $this->findGroupOrFail($request->groupId, false);
        
        $shared = !$request->shared ? null
            : $this->sharesService->prepareShared($requester, $request->shared['type'], $request->shared['id']);
        $group->createPost(
            $requester, 
            $request->text, 
            (bool)$request->disableComments, 
            $shared, 
            [],//$this->attachmentsService->prepareAttachmentsForGroupPost($requester, $request->attachments), 
            (bool)$request->onBehalfOfGroup,
            (bool)$request->addSignature,
        );
        return new CreateResponse("OK");
    }
    
    private function validateRequest(CreateRequest $request): void {     

    }
}
