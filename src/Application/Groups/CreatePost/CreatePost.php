<?php
declare(strict_types=1);
namespace App\Application\Groups\CreatePost;

use App\Application\Groups\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Groups\Post\PostRepository;
use App\Domain\Model\Groups\Group\GroupRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Common\AttachmentsService;

class CreatePost extends PostAppService {

    private AttachmentsService $attachmentsService;
    
    function __construct(
        UserRepository $users,
        GroupRepository $groups,
        PostRepository $posts,
        AttachmentsService $attachmentsService
    ) {
        parent::__construct($posts, $groups, $users);
        $this->attachmentsService = $attachmentsService;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $group = $this->findGroupOrFail($request->groupId);
        
        $this->validateRequest($request);
        
        $attachments = [];
        foreach ($request->attachmentsIds as $attachmentId) {
            $attachments[] = $this->attachmentsService->prepareProfilePostAttachment($requester, $attachmentId);
        }
        
        $shareable = null;
        if($request->shared) {
            $shareable = $this->prepareShareable($requester, $request->shared['type'], $request->shared['id']);
        }
        $shared = $shareable ? $shareable->accept(new CreateSharedVisitor($requester)) : null;
        
        $group->createPost(
            $requester,
            $request->text,
            $request->disableComments,
            $shared,
            $attachments,
            $request->asGroup,
            $request->signature
        );
    }
}