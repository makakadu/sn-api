<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\Domain\Model\Users\Post\Post;
use App\DTO\Users\UserPostDTO;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Common\Reaction;
use App\DataTransformer\SharedTransformer;
use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Criteria;
use App\DTO\Users\PostCommentDTO;
use App\Domain\Model\Users\Post\Comment\CommentRepository;
use App\DTO\Users\ActiveProfileDTO;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Users\Subscription\SubscriptionRepository;

class ProfileTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;

    private ConnectionRepository $connections;
    private SubscriptionRepository $subscriptions;
    private PostRepository $posts;
    
    public function __construct(ConnectionRepository $connections, SubscriptionRepository $subscriptions, PostRepository $posts) {
        $this->connections = $connections;
        $this->subscriptions = $subscriptions;
        $this->posts = $posts;
    }
    
    function transformMultiple(?User $requester, array $profiles, ?array $fields): array {
        $dtos = [];
        if(\count($fields)) {
            foreach($profiles as $profile) {
                $dtos[] = $this->transformPartial($requester, $profile, $fields);
            }
        } else {
            foreach($profiles as $profile) {
                $dtos[] = $this->transform($requester, $profile);
            }
        }
        return $dtos;
    }
    
    function transformMultipleToSubscriberDTO(array $profiles): array {
        $dtos = [];
        foreach($profiles as $profile) {
            $picture = $profile->currentPicture();
            
            $dtos[] = new \App\DTO\Users\SubscriberDTO(
                $profile->id(),
                $picture ? $picture->small() : null,
                $profile->firstName(), 
                $profile->lastName(),
                (string)$profile->username()
            );
        }
        return $dtos;
    }
    
    function transform(?User $requester, User $profile): ActiveProfileDTO {

        $currentPicture = $profile->currentPicture();
        $cover = $profile->currentCover();
        
        $birthday = $profile->birthday();
        $birthdayDTO = null;
        switch ($profile->birthdayAppearance()) {
            case User::SHOW_WITHOUT_YEAR:
                $birthdayDTO = ['day' => $birthday->format('d'), 'month' => $birthday->format('m')];
            case User::SHOW:
                $birthdayDTO = ['day' => $birthday->format('d'), 'month' => $birthday->format('m'), 'year' => $birthday->format('Y')];
            case User::HIDE: // это не обязательно, ведь по умолчанию birthday уже равно null
                //$birthdayDTO = null;
        }
        
//        $connected = false;
//        $sentConnectionRequest = false;
//        $receivedConnectionRequest = false;
        
        $connectionDTO = null;
        $subscriptionDTO = null;
        $banned = false;
        $acceptMessages = false; // Это может быть запрещено настройками приватности, но только, если диалога между пользовалями еще не существует. Если же существует
        // то настройки приватности не будут работать. Поэтому нужно 
        if($requester) {
            $connection = $this->connections->getByUsersIds($requester->id(), $profile->id());
            $connectionDTO = null;
            
            if($connection) {
                $connsTrans = new ConnectionTransformer();
                $connectionDTO = $connsTrans->transform($connection);
            }
            
            $subscriptionDTO = null;
            $subscription = $this->subscriptions->getByUsersIds($requester->id(), $profile->id());
            
            if($subscription) {
                $subscriptionsTrans = new SubscriptionTransformer();
                $subscriptionDTO = $subscriptionsTrans->transform($subscription);
            }
            $banned = $profile->inBlacklist($requester);
            $acceptMessages = false;
        }
        
        $postsCount = 0;//$this->posts->getCountOfActiveAndAccessibleToRequesterByOwner($requester, $profile);
        
        $picturesDTOs = [];
        foreach($profile->pictures() as $picture) {
            $picturesDTOs[] = new \App\DTO\Users\PictureDTO(
                $picture->id(),
                $picture->versions(),
                null,
                $this->creationTimeToTimestamp($picture->createdAt())
            );
        }
        $coversDTOs = [];
        $coverTrans = new CoverTransformer();
        foreach($profile->covers() as $cover) {
            $coversDTOs[] = $coverTrans->transform($cover);
        }
        
        return new ActiveProfileDTO(
            $profile->id(),
            $currentPicture ? new \App\DTO\Users\PictureDTO(
                $currentPicture->id(),
                $currentPicture->versions(),
                null,
                $this->creationTimeToTimestamp($currentPicture->createdAt())
            ) : null,
            $cover ? (new CoverTransformer())->transform($cover) : null,
            $profile->firstName(),
            $profile->lastName(),
            (string)$profile->username(),
            $profile->gender(),
            $birthdayDTO,
            '',
            '',
            $connectionDTO,
            $subscriptionDTO,
            $banned,
            "",
            $acceptMessages,
            $postsCount,
            $picturesDTOs,
            $coversDTOs
        );
    }
    
    function transformPartial(User $requester, User $user, array $fields): \App\DTO\Users\PartialProfileDTO {
        $dto = new \App\DTO\Users\PartialProfileDTO($user->id());        
        
        if(\in_array('firstname', $fields)) {
            $dto->firstName = $user->firstName();
        }
        if(\in_array('lastname', $fields)) {
            $dto->lastName = $user->lastName();
        }
        if(\in_array('picture', $fields)) {
            $currentPicture = $user->currentPicture();
            $pictureDTO = $currentPicture ? new \App\DTO\Users\PictureDTO(
                $currentPicture->id(),
                $currentPicture->versions(),
                null,
                $this->creationTimeToTimestamp($currentPicture->createdAt())
            ) : null;
            $dto->picture = $pictureDTO;
        }
        if(\in_array('cover', $fields)) {
            $dto->cover = $user->currentCover();
        }
        if(\in_array('username', $fields)) {
            $dto->username = (string)$user->username();
        }
        if(\in_array('gender', $fields)) {
            $dto->gender = $user->gender();
        }
        if(\in_array('city', $fields)) {
            $dto->gender = "";
        }
        if(\in_array('country', $fields)) {
            $dto->gender = "";
        }
        if(\in_array('status', $fields)) {
            $dto->status = "";
        }
        if(\in_array('connection', $fields)) {
            $connectionDTO = null;
            if($requester) {
                $connection = $this->connections->getByUsersIds($requester->id(), $user->id());

                if($connection) {
                    $connsTrans = new ConnectionTransformer();
                    $connectionDTO = $connsTrans->transform($connection);
                }
            }
            $dto->connection = $connectionDTO;
        }
        if(\in_array('subscription', $fields)) {
            $subscriptionDTO = null;
            if($requester) {
                $subscription = null; //$this->subscriptions->getByUsersIds($requester->id(), $profile->id());
            
                if($subscription) {
                    $subscriptionsTrans = new SubscriptionTransformer();
                    $subscriptionDTO = $subscriptionsTrans->transform($subscription);
                }
            }
            $dto->subscription = $subscriptionDTO;
        }
        if(\in_array('banned', $fields)) {
            $dto->banned = $user->inBlacklist($requester);
        }
        if(\in_array('acceptMessages', $fields)) {
            $dto->acceptMessages = true;
        }
        if(\in_array('postsCount', $fields)) {
            $dto->postsCount = 1234;
        }
        return $dto;
    }

}