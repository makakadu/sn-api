<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\CreateComment;

use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Pages\Page\PageRepository;
use App\Domain\Model\Users\AttachmentsService;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Post\PostRepository;
use App\Domain\Model\Authorization\PagesAuth;
use App\Domain\Model\Users\Post\Comments\CommentRepository;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Exceptions\ValidationException;
use App\Application\Exceptions\MalformedRequestException;

class CreateComment implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\Posts\PostAppServiceTrait;
    use \App\Application\Pages\PageAppService;
    
    private AttachmentsService $attachmentsService;
    
    public function __construct(
        UserRepository $users, 
        AttachmentsService $attachmentsService,
        PostRepository $posts,
        PageRepository $pages
    ) {
        $this->users = $users;
        $this->posts = $posts;
        $this->pages = $pages;
        $this->attachmentsService = $attachmentsService;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        //$this->validateRequest($request);
        
        $post = $this->findPostOrFail($request->postId, false);

        $page = null;
        if($request->asPage) {
            $page = $this->findPageOrFail($request->asPage, false, "Cannot comment on behalf of page {$request->asPage} because in not found");
        }
        $attachment = $request->attachment
            ? $this->attachmentsService->prepareProfileCommentAttachment($requester, $request->attachment)
            : null;
        
        $post->comment($requester, $request->text, $request->repliedId, $attachment, $page);
        //echo 'OK';exit();
        return new CreateCommentResponse('ok');
    }
    
}