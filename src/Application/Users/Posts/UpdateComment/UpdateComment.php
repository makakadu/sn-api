<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\UpdateComment;

use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\AttachmentsService;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Post\PostRepository;
use App\Application\Exceptions\MalformedRequestException;
use App\Domain\Model\Users\Post\Comment\CommentRepository as PostCommentRepository;

class UpdateComment implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Posts\PostAppServiceTrait;
    use \App\Application\Users\Posts\PostCommentAppService;
    
    function __construct(
        UserRepository $users,
        PostRepository $posts,
        UserPostsAuth $postsAuth,
        PostCommentRepository $comments
    ) {
        $this->users = $users;
        $this->posts = $posts;
        $this->postsAuth = $postsAuth;
        $this->comments = $comments;
    }
    
    public function execute(BaseRequest $request): UpdateCommentResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $comment = $this->findCommentOrFail($request->commentId, true);

        if($request->property === 'deleted') {
            $request->value
                ? $comment->delete(false)
                : $comment->restore(false);
        }
        else {
            throw new \App\Application\Exceptions\UnprocessableRequestException(123, "Incorrect property name");
        }

        return new UpdateCommentResponse('OK');
    }
    
    public function executeOld(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $post = $this->findPostOrFail($request->postId, false);
        $comment = $post->getCommentById($request->commentId);
        if(!$comment) {
            throw new NotFoundException("Comment not found");
        }
        $this->postsAuth->failIfCannotEditComment($requester, $comment);

        foreach ($request->payload as $key => $value) {
            if($key === 'is_deleted') {
                if(count($request->payload) > 1) {
                    throw new UnprocessableRequestException(123, "If property 'is_deleted' is updating then other properies cannot be updated in same request");
                }
                $this->validateParamIsDeleted($value);
                $value ? $comment->delete(false) : $comment->restore();
            }
            elseif($key === 'text') {
                $this->validateParamText($value);
                $comment->changeText($value);
            }
            elseif($key === 'attachment') {
                $this->validateParamAttachment($value);
                $type = $value['type'];
                $id = $value['id'];
                

                if($type === 'temp-photo') {
                    $tempPhoto = $this->attachmentsService->prepareTempPhoto($id);
                    $comment->createPhotoAndAttach($this->photoService->createPhotoVersionsFromPhoto($tempPhoto));
                }
                if($type === 'comment-photo') {
                    $photo = $comment->photo();
                    if(!$photo || $photo->id() !== $id) {
                        throw new UnprocessableRequestException(123, "Photo with type 'comment-photo' should be already added to updating comment, but 'comment-photo' with id $id was not found in comment");
                    }
                    $comment->addPhoto($photo);
//                    $photo = $this->attachmentsService->preparePhotoForUserComment($id);
//                    $comment->changePhoto($photo);
                }
                elseif($type === 'video') {
                    $video = $this->attachmentsService->prepareVideoForProfileComment($requester, $comment, $id);
                    $comment->addVideo($video);
                } else {
                    throw new MalformedRequestException("Incorrect attachment type");
                }
            }
        }
        
        return new UpdateCommentResponse('Comment updated');
    }

}
