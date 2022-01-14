<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\PutComment;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\AttachmentsService;
use App\Application\Users\Posts\PostAppService;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Users\Post\Comment\CommentRepository;
use App\Domain\Model\Users\Comments\AttachmentRepository;

class PutComment implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Posts\PostAppServiceTrait;
    use \App\Application\Users\Posts\PostCommentAppService;
            
    private UserPostsAuth $postsAuth;
    private AttachmentRepository $attachments;
    
    function __construct(
        UserRepository $users,
        CommentRepository $comments,
        UserPostsAuth $postsAuth,
        AttachmentRepository $attachments
    ) {
        $this->attachments = $attachments;
        $this->postsAuth = $postsAuth;
        $this->users = $users;
        $this->comments = $comments;
    }  

    public function execute(BaseRequest $request): PutCommentResponse {
        //$this->validateRequest($request);
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $comment = $this->findCommentOrFail($request->commentId, true);
        
        //$this->postsAuth->failIfCannotEdit($requester, $post);
        $attachment = $request->attachment
            ? $this->attachments->getById($request->attachment) : null;
        
        $comment->edit($request->text, $attachment);

        return new PutCommentResponse('OK');
    }
    
//    private function validateRequest(CreatePostRequest $request): void {     
//        $this->validateParamText($request->text);
//        $this->validateParamAttachments($request->attachments);
//        $this->validateParamDisableComments($request->disableComments);
//        $this->validateParamPublic($request->public);
//    }
}
