<?php
declare(strict_types=1);
namespace App\Application\Pages\SuggestedPost\Patch;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Post\SuggestedPost\SuggestedPostRepository;

class Patch implements \App\Application\ApplicationService { 
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\SuggestedPost\SuggestedPostAppService;

    public function __construct(SuggestedPostRepository $posts, UserRepository $users) {
        $this->suggestedPosts = $posts;
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): PatchResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $post = $this->findSuggestedPostOrFail($request->postId, true);

        foreach ($request->payload as $key => $value) {
            if($key === 'deleted') {
                $value
                    ? $post->delete($requester)
                    : $post->restore($requester);
            }
            elseif($key === 'rejected') {
                $value
                    ? $post->reject($requester)
                    : $post->undoRejection($requester);
            } else {
                throw new \App\Application\Exceptions\UnprocessableRequestException(123, "Incorrect param name");
            }
        }

        return new PatchResponse('OK');
    }

}
