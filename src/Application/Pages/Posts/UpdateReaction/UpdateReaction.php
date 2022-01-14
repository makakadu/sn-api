<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\UpdateReaction;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Post\PostRepository;

class UpdateReaction implements \App\Application\ApplicationService {
    use \App\Application\Pages\Posts\PostAppServiceTrait;
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\PageAppService;
    
    public function __construct(UserRepository $users, PostRepository $posts) {
        $this->users = $users;
        $this->posts = $posts;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        //$this->validateParamReactionType($request->reactionType);
        
        $post = $this->findPostOrFail($request->postId, false);
        $post->editReaction($requester, $request->reactionId, $request->type);
        
        return new UpdateReactionResponse('OK');
    }

}
