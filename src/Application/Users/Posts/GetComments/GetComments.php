<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetComments;

use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\Comment\CommentRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\DataTransformer\Users\PostTransformer;

class GetComments implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private PostRepository $posts;
    private CommentRepository $comments;
    private PostTransformer $postsTransformer;
    
    public function __construct(UserRepository $users, PostRepository $posts, PostTransformer $postsTransformer, CommentRepository $comments) {
        $this->users = $users;
        $this->posts = $posts;
        $this->comments = $comments;
        $this->postsTransformer = $postsTransformer;
    }
    
    public function execute(BaseRequest $request): BaseResponse {               
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
//        $post = $this->findPostOrFail($request->postId, false);
//        $this->postsAuthorization->failIfAccessToPostProhibited($requester, $post);

        $post = $this->posts->getById($request->postId);
        if(!$post) {
            throw new \App\Application\Exceptions\NotExistException('Post not found');
        }
        $comments = $this->comments->getPartOfActiveByPost(
            $post,
            $request->offsetCommentId,
            $request->limit ?? 20,
            $request->type ?? 'root',
            $request->order ?? 'DESC'
        );
        $count = $this->comments->getCountOfActiveByPost($post);
        
        $dtos = $this->postsTransformer->postCommentsToDTO($requester, $comments);
        
        return new GetCommentsResponse($dtos, $count);
    }
}
/*
 * Если комменты отключены, но комменты уже были оставлены, то возвратятся они, если их нет, то они не возвратятся. Логично
 * Если кто-то запрашивает комменты, то неправильно возвращать инфу о том, что комментов нет, лучше просто их не возвратить. Если же нужно узнать отключены комменты ли нет,
 * то нужно запросить сам пост
 * 
 */
/*
    public function execute2(?string $requesterId, int $postId): GetUserPostResponse {
        $post = $this->findPostNotAsTargetOrFail($postId); // Когда получаем коллекцию и она пуста, то это НЕ 404. Может быть, что поста не существует, тогда это 422, как мне кажется
        $requester = $requesterId ?? $this->users->getById($requesterId);
        
        if(($requester && $this->isRequesterInactive($requester)) || !$requester) {
            $this->failIfGuestHasNoAccessToPost($post);
        } else {
            $this->failIfUserHasNoAccessToPost($requester, $post);
        }
        
        return new GetUserPostResponse($post);
    }
    

    // Мне кажется, что не стоит передавать null в метод failIfRequesterHasNoAccessToPost, потому что это может привести к большим проблема чем дублирование этого кода в нескольких use cases
    // Но с другой стороны если передаётся null, то он ведь не с пустого места взялся, он был создан исходя из некоторой логике.
    // Вряд ли можно случайно явно передать null сюда. К тому же есть тестирование
    protected function failIfUserHasNoAccessToPost(User $requester, UserPost $post) {
        $this->failIfBannedByOwner($requester, $post->creator());
        if($post->creator()->isClosed() && !\in_array($requester, $post->creator()->friends())) {
            throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY);
        }
    }
    
    protected function failIfGuestHasNoAccessToPost($post) {
        if($post->creator()->isClosed() || $post->creator()->isHidden()) {
            throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY);
        }
    }

    /*
    private function canGuestViewCreatedByOwner(User $owner): bool {
        return !$owner->isClosed() && !$owner->isHidden();
    }
    
    protected function findPostOwnerOrFail(UserId $ownerId): User {
        $owner = $this->users->getById($ownerId);
        if(!$owner) {
            throw new NotExistException('Post not found');
        }
    }
    
    protected function failIfPostNotAccessibleFor(?User $requester, UserPost $post) {
        $owner = $post->creator(); // здесь, наверное, try catch нужон
        $this->failIfOwnerIsInactive($owner);
        
        if($requester) {
            $this->failIfBannedByOwner($requester, $owner);
        }
        $ownerIsCreator = $post->ownerId()->equals($post->creatorId());
        $isRequesterAGuest = $this->isRequesterInactive($requester) || !$requester;
        
        if($isRequesterAGuest && $ownerIsCreator) {
            $isAccessAllowed = $this->canGuestViewCreatedByOwner($owner);
        } else if($isRequesterAGuest && !$ownerIsCreator) {
            $isAccessAllowed = $this->isAccessibleForGuests($owner, CPPS::NOT_OWN_POSTS);
        } else if(!$isRequesterAGuest) {
            $isAccessAllowed = $ownerIsCreator ? !$owner->isClosed()
                : $owner->isAllowedByPrivacyTo($requester, CPPS::NOT_OWN_POSTS);
        }
        
        if(!$isAccessAllowed) {
            throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY);
        }
    }
    
    // Для постов, у которых создатель является владельцем, нельзя настроить приватность
    // Для пользователей, которые заблочены, приостановлены, удалены или неаутентифицированные, подходит один алгоритм действий.
    // Для постов, созданных владельцем, нельзя настроить приватность, поэтому
    
    // Если страница скрыта, то неаутентифицированные(также приостановлены, удалённые и заблоченные) не могут видеть вообще никакие посты владельца
    // Если странице НЕ скрыта, но закрыта, то посты созданные владельцем будут доступны только друзьям
    // Если страница НЕ закрыта и НЕ скрыта, то посты созданные владельцем будут доступны всем
    // Если страница НЕ скрыта, то посты созданные НЕ владельцем будут доступны тем, кому разрешено в настройках приватности
//}
*/
