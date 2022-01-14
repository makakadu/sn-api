<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\CreateComment;

use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Pages\Page\PageRepository;
use App\Domain\Model\Users\AttachmentsService;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Authorization\PagesAuth;
use App\Domain\Model\Users\Post\Comments\CommentRepository;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Exceptions\ValidationException;
use App\Application\Exceptions\MalformedRequestException;
use App\Domain\Model\Users\Comments\AttachmentRepository;

class CreateComment extends PostAppService {
    use \App\Application\Pages\PageAppService;
    
    private PagesAuth $pagesAuth;
    private AttachmentsService $attachmentsService;
    private AttachmentRepository $attachments;
    
    function __construct(
        UserRepository $users,
        PostRepository $posts,
        UserPostsAuth $postsAuth,
        PageRepository $pages,
        PagesAuth $pagesAuth,
        AttachmentsService $attachmentsService,
        AttachmentRepository $attachments
    ) {
        parent::__construct($posts, $users, $postsAuth);
        $this->pages = $pages;
        $this->pagesAuth = $pagesAuth;
        $this->attachmentsService = $attachmentsService;
        $this->attachments = $attachments;
    }

    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        //$this->validateRequest($request);
        
        $post = $this->findPostOrFail($request->postId, false);
         // В настройках приватности можно запретить комментировать посты, даже если они доступны
        
        $page = null;
        if($request->onBehalfOfPage) {
            $page = $this->findPageOrFail($request->onBehalfOfPage, false, null);
        }
        $this->postsAuth->failIfCannotComment($requester, $post, $page);

//        $attachment = $request->attachment
//            ? $this->attachmentsService->prepareProfileCommentAttachment($requester, $request->attachment)
//            : null;
        $attachment = $request->attachment
            ? $this->attachments->getById($request->attachment) : null;
        
        $comment = $post->comment($requester, $request->text, $request->repliedId, $attachment, $page);
        
        return new CreateCommentResponse($comment->id());
    }
    
}