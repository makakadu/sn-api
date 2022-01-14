<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\Put;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\AttachmentsService;
use App\Application\Users\Posts\PostAppService;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Users\Post\AttachmentRepository as PostAttachmentRepository;

class Put  implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Posts\PostAppServiceTrait;
            
    private PostAttachmentRepository $attachments;
    private UserPostsAuth $postsAuth;
            
    function __construct(
        UserRepository $users,
        PostRepository $posts,
        UserPostsAuth $postsAuth,
        PostAttachmentRepository $attachments
    ) {
        $this->attachments = $attachments;
        $this->postsAuth = $postsAuth;
        $this->users = $users;
        $this->posts = $posts;
    }  

    public function execute(BaseRequest $request): PutResponse {
        //$this->validateRequest($request);
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $post = $this->findPostOrFail($request->postId, true);
        
        $this->postsAuth->failIfCannotEdit($requester, $post);
        
        $post->edit(
            $request->text,
            (bool)$request->disableComments,
            (bool)$request->disableReactions,
            $this->attachments->getByIds($request->attachments),
            (bool)$request->isPublic
        );

        return new PutResponse('OK');
    }
    
//    private function validateRequest(CreatePostRequest $request): void {     
//        $this->validateParamText($request->text);
//        $this->validateParamAttachments($request->attachments);
//        $this->validateParamDisableComments($request->disableComments);
//        $this->validateParamPublic($request->public);
//    }
}
