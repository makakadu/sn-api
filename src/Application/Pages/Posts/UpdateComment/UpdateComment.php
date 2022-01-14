<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\UpdateComment;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Post\Comment\CommentRepository as PostCommentRepository;
use App\Domain\Model\Users\AttachmentsService;
use App\Domain\Model\Pages\Comments\AttachmentRepository;

class UpdateComment implements \App\Application\ApplicationService {
    use \App\Application\Pages\Posts\PostCommentAppServiceTrait;
    use \App\Application\AppServiceTrait;
    
    public function __construct(PostCommentRepository $comments, UserRepository $users, AttachmentRepository $attachments) {
        $this->comments = $comments;
        $this->users = $users;
        $this->attachments = $attachments;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        //$this->validateParamReactionType($request->reactionType);
        
        $comment = $this->findCommentOrFail($request->commentId, false);
        $comment->edit(
            $requester, $request->text,
            $request->attachmentId ? $this->attachments->getById($request->attachmentId) : null
        );
        
        return new UpdateCommentResponse('OK');
    }

}
