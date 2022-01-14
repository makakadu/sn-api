<?php
declare(strict_types=1);
namespace App\Application\Groups\Posts\CreateComment;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\AttachmentsService;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Groups\Post\PostRepository;

class CreateComment implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Groups\Posts\PostAppServiceTrait;
    use \App\Application\Groups\GroupAppServiceTrait;
    
    private AttachmentsService $attachmentsService;
    
    public function __construct(
        UserRepository $users, 
        AttachmentsService $attachmentsService,
        PostRepository $posts
    ) {
        $this->users = $users;
        $this->posts = $posts;
        $this->attachmentsService = $attachmentsService;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        //$this->validateRequest($request);
        
        $post = $this->findPostOrFail($request->postId, false);

        $attachment = null;//$request->attachment
            //? $this->attachmentsService->prepareGroupCommentAttachment($requester, $request->attachment)
            //: null;
        
        $post->comment($requester, $request->text, $request->repliedId, $attachment, (bool)$request->onBehalfOfGroup);
        //echo 'OK';exit();
        return new CreateCommentResponse('ok');
    }
    
}