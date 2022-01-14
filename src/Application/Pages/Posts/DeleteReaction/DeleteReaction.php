<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\DeleteReaction;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Pages\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Application\Exceptions\NotExistException;

class DeleteReaction  implements \App\Application\ApplicationService {
    use \App\Application\Pages\Posts\PostAppServiceTrait;
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\PageAppService;
    
    public function __construct(UserRepository $users, PostRepository $posts) {
        $this->users = $users;
        $this->posts = $posts;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $post = $this->findPostOrFail($request->postId, false);
        
        $post->deleteReaction($requester, $request->reactionId);
        
        return new DeleteReactionResponse('ok');
    }
}
