.<?php
declare(strict_types=1);
namespace App\Application\Groups\CreatePost;

use App\Application\Users\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Application\RequestParamsValidator;
use App\Domain\Model\Authorization\ProfilePostsAuthorization;
use App\Domain\Model\Users\Post\PostRepository as ProfilePostRepository;
use App\Domain\Model\Common\Shares\SharesService;
use App\Domain\Model\Authorization\SharesAuthorization;
use App\Domain\Model\Users\Post\PostAttachmentsService;

class CreatePostComment extends PostAppService {
    
    private CommentAttachmentsService $attachmentsService;
            
    function __construct(
        UserRepository $users,
        RequestParamsValidator $validator,
        SharesService $sharesService,
        SharesAuthorization $sharesAuthorization,
        ProfilePostsAuthorization $postsAuthorization,
        ProfilePostRepository $profilePosts,
        CommentAttachmentsService $attachmentsService
    ) {
        parent::__construct($profilePosts, $users, $validator, $postsAuthorization);
        $this->sharesService = $sharesService;
        $this->sharesAuthorization = $sharesAuthorization;
        $this->attachmentsService = $attachmentsService;
    }

    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $this->validateRequest($request);
        
        $group = $this->findGroupOrFail($request->groupId);
        
        $attachments = $this->attachmentsService->prepareAttachmentsForGroupPost(
            $requester, $group, $request->attachmentsPaths
        );
        
        $post = $group->createPost($requester, $request->text, ...$attachments);
        $this->posts->add($post);
        $this->posts->flush();
        
        return new CreatePostResponse('ok');
    }
            
    private function validateRequest(CreatePostRequest $request): void {        
        $this->validateText($request->text);
        $this->validateAttachments($request->attachments);
        $this->validateAreCommentsDisabled($request->disableComments);
    }
}
