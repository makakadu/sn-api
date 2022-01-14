<?php
declare(strict_types=1);
namespace App\Application\Groups\Posts\CreateReaction;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Groups\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;

class CreateReaction implements \App\Application\ApplicationService {
    use \App\Application\Groups\Posts\PostAppServiceTrait;
    use \App\Application\AppServiceTrait;
    
    public function __construct(UserRepository $users, PostRepository $posts) {
        $this->users = $users;
        $this->posts = $posts;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $post = $this->findPostOrFail($request->postId, true);
        $post->react($requester, $request->type, (bool)$request->onBehalfOfGroup);

        return new CreateReactionResponse('ok');
    }
    
    function validate(GetRequest $request): void {
        ReactionValidator::validateTypeParam($request->type);
        ReactionValidator::validateAsPageIdParam($request->asPageId);
    }
}
