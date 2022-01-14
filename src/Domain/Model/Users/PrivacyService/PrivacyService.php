<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\PrivacyService;

use App\Domain\Model\Users\AccessLevels as AL;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Privacy;
use App\Domain\Model\Users\User\ProfilePrivacySettings as PS;
use App\Domain\Model\Users\Albums\Album;
use App\Application\Errors;
use App\Domain\Model\Users\Post\Post;
use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\Domain\Model\Users\ConnectionsList\ConnectionsListRepository;
use App\Domain\Model\Users\Videos\Video;
use App\Domain\Model\Users\Post\Comment\Comment as PostComment;
use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;
use Doctrine\Common\Collections\ArrayCollection;
use Assert\Assertion;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Exceptions\MalformedRequestException;
use App\Domain\Model\Users\ComplexPrivacySetting;

class PrivacyService {
    
    private ConnectionRepository $connections;
    private ConnectionsListRepository $connectionsLists;
    private PrivacyResolver $resolver;
    
    function __construct(ConnectionRepository $connections, ConnectionsListRepository $connectionsLists, PrivacyResolver $resolver) {
        $this->connections = $connections;
        $this->connectionsLists = $connectionsLists;
        $this->resolver = $resolver;
    }
    
    function guestsHaveAccessToProfile(User $user): bool {
        return true;//(!$user->isClosed() && !$user->isHidden());
    }
    
    function userHasAccessToProfile(User $requester, User $owner): bool {
        return $this->areUsersFriends($requester, $owner); // если профиль не закрыт, то доступ до профиля не закрыт настройками приватности для запрашивающего
        //(но не факт, что запрашивающий не забанен, но это уже не забота privacy сервиса)
        // Если же профиль закрыт, то к нему будет открыт доступ только, если запрашивающий и владелец профиля являются друзьями
    }
    
    function isAllowedTo(User $requester, User $requestee, string $settingName): bool {
        return $this->resolver->isAllowedByPrivacy($requestee, $requester, $settingName, $this->connections);
    }
    
    function hasAccess(ComplexPrivacySetting $setting, User $requester): bool {
        return $this->resolver->hasAccess($setting, $requester, $this->connections);
    }
    
    function canOfferFriendship(User $requester, User $requestee): bool {
//        $whoCanOffer = $requestee->privacy()->whoCanOfferFriendship();
//        
//        if($whoCanOffer[Privacy::ACCESS_LEVEL] === AL::CONNECTIONS_OF_CONNECTIONS) {
//            if(!$this->connections->haveCommonFriend($requester->id(), $requestee->id())) {
//                return false;
//            }
//        }
        return true;
    }
    
    function canSeeVideo(User $requester, Video $video): bool {
        return $this->resolver->canSeeVideo($requester, $video, $this->connections);
    }
    
    function canGuestSeeVideo(Video $video): bool {
        $videoOwner = $video->owner();
        $this->guestsHaveAccessToProfile($videoOwner);
        return $this->guestsHaveAccessToProfile($videoOwner) && ($video->whoCanSee()->accessLevel() === AL::EVERYONE);
    }
    
    function canSeeAlbum(User $requester, Album $album): bool {
        return $this->resolver->canSeeAlbum($requester, $album, $this->connections);
    }
    
    function canSeePost(User $requester, \App\Domain\Model\Users\Post\Post $post): bool {
        return true;//$this->resolver->canSeePost($requester, $post, $this->connections);
    }
    
    function canGuestSeeAlbum(Album $album): bool {
        return true;
    }
    
    function canUserCommentAlbum(User $requester, Album $album): bool {
        return true;
    }
    
    function guestsCanSeeAlbumPhotos(Album $album): bool {
        $albumCreator = $album->user();
        //$isProfileOpenForGuests = !$albumCreator->isClosed() && !$albumCreator->isHidden();
        return $album->whoCanSee()->accessLevel() === AL::EVERYONE;
    }

    function isProfileAccessibleTo(User $requester, User $profileOwner): bool {
        return $this->areUsersFriends($requester, $profileOwner);
    }
    
    function areUsersFriends(User $user1, User $user2): bool {
        return (bool)$this->connections->getByUsersIds($user1->id(), $user2->id());
    }

    function failIfCannotUpdatePostComment(User $requester, PostComment $comment): void {
        if(!$comment->creator()->equals($requester)) {
            throw new \App\Application\Exceptions\ForbiddenException(Errors::NO_RIGHTS, 'Чужой пост');
        }
        $this->failIfAccessToPostProhibited($requester, $comment->commentedPost());            
    }
    
    function failIfAccessToPostProhibited(?User $requester, Post $post): void {
        $postCreator = $post->creator();
        $this->failIfResourceOwnerIsInactive($postCreator);
        
        if($requester) {
            if($requester->equals($post->creator())) {
               return; 
            } 
            $this->failIfBannedByResourceOwner($requester, $postCreator);
            
            if(!$this->areUsersFriends($requester, $postCreator)) {
                throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, '');
            }
        } else {
            if($postCreator->isHidden()) {
                throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, '');
            }
        }
    }
    
    function failIfResourceOwnerIsInactive(User $owner): void {
        if($this->isUserInactive($owner)) {
            $inactivityReason = $this->getReasonOfUserInactivity($owner);
            throw new ForbiddenException(Errors::INACTIVE,"Access to resource forbidden, owner was $inactivityReason");
        }
    }
    
    function isUserInactive(User $user): bool {
        return false;
    }
    
    function getReasonOfUserInactivity(User $user): string {
        return 'kek';
    }
    
    function failIfBannedByResourceOwner(User $requester, User $owner): void {
        if($owner->inBlacklist($requester)) {
            throw new ForbiddenException(Errors::BANNED_BY_USER, '');
        }
    }
    
//    // can see album - это универсальный метод, его можно использовать при проверке доступа и к альбмоу и к фото, потому что всё сводится к проверке доступа к альбому
//    function canSeeAlbum(User $requester, Album $album): bool {        
//        return $album->accept(new CanSeeAlbumVisitor($requester, $this->resolver));
//    }
//    
    // я такая же х***я
//    function canGuestSeeAlbum(Album $album): bool {        
//        return $album->accept(new CanGuestsSeeAlbumVisitor($this->resolver));
//    }
    
//    /**
//     * @param array<string> $connectionsIds
//     * @return ArrayCollection<int, Connection>
//     */
//    function findConnections(array $connectionsIds): ArrayCollection {
//        $connections = $this->connections->getByIds($connectionsIds);
//        $prepared = new ArrayCollection();
//        foreach ($connections as $connection) {
//            $prepared[] = $connection;
//        }
//        return $prepared;
//    }
    
    /**
     * @param array<string> $connectionsIds
     * @return array<Connection>
     */
    function findConnections(array $connectionsIds): array {
        return $this->connections->getByIds($connectionsIds);
    }
//    
//    /**
//     * @param array<string> $listsIds
//     * @return ArrayCollection<int, ConnectionsList>
//     */
//    function findConnectionsLists(array $listsIds): ArrayCollection {
//        $connectionsLists = $this->connectionsLists->getByIds($listsIds);
//        $prepared = new ArrayCollection();
//        foreach ($connectionsLists as $list) {
//            $prepared[] = $list;
//        }
//        return $prepared;
//    }
    
    /**
     * @param array<string> $listsIds
     * @return array<ConnectionsList>
     */
    function findConnectionsLists(array $listsIds): array {
        return $this->connectionsLists->getByIds($listsIds);
    }
    
    /**
     * @param array<array> $lists
     * @return array<mixed>
     */
    function prepareLists(array $lists): array {
        $allowedConnections = isset($lists['allowed_connections']) ? $lists['allowed_connections'] : [];
        $unallowedConnections = isset($lists['unallowed_connections']) ? $lists['unallowed_connections'] : [];
        $allowedLists = isset($lists['allowed_lists']) ? $lists['allowed_lists'] : [];
        $unallowedLists = isset($lists['unallowed_lists']) ? $lists['unallowed_lists'] : [];
        
        $prepared = [
            'allowed_connections' => $this->findConnections($allowedConnections),
            'unallowed_connections' => $this->findConnections($unallowedConnections),
            'unallowed_lists' => $this->findConnectionsLists($unallowedLists),
            'allowed_lists' => $this->findConnectionsLists($allowedLists),
            
        ];
        return $prepared;
    }
    
    /**
     * @param mixed $privacySettingData
     * @throws MalformedRequestException
     * @throws UnprocessableRequestException
     */
    function checkPrivacyData(string $privacySettingName, $privacySettingData): void {
        try {
            Assertion::isArray($privacySettingData, "'$privacySettingName' parameter should be an array");
            Assertion::keyExists($privacySettingData, 'access_level', "Incorrect structure of '$privacySettingName' param, '$privacySettingName' should contain 'access_level' property");
            Assertion::integer($privacySettingData['access_level'], "Incorrect structure of '$privacySettingName' param, property 'access_level' should be an integer");

            Assertion::keyExists($privacySettingData, 'lists', "Incorrect structure of '$privacySettingName' param, '$privacySettingName' should contain 'lists' property");
            Assertion::isArray($privacySettingData['lists'], "Incorrect structure of '$privacySettingName' param, property 'lists' should be array");
            
            $allowedConnections   = $privacySettingData[Privacy::ALLOWED_CONNECTIONS]   ?? [];      // Если в массиве нет такого ключа, то всё ок, допускается ничего вместо пустого массива, чтобы запрос был не таким огромным
            $unallowedConnections = $privacySettingData[Privacy::UNALLOWED_CONNECTIONS] ?? [];
            $allowedLists    = $privacySettingData[Privacy::ALLOWED_LISTS]    ?? [];
            $unallowedLists  = $privacySettingData[Privacy::UNALLOWED_LISTS]  ?? [];
            /*
             * Лучше всего пересечения проверить здесь, потому что здесь это делается перед извлечением connections из БД,
             * если здесь будет дублирование, то выполнение остановится и не будет сделано лишних запросов
             */
            $connectionsIntersections = array_intersect($allowedConnections, $unallowedConnections);
            //print_r($privacySettingData);exit();
            if(count($connectionsIntersections)) {
                $connectionId = $connectionsIntersections[0];
                throw new UnprocessableRequestException(
                    1, "Incorrect structure of '$privacySettingName' param, id '$connectionId'"
                    . " cannot be in 'allowed_connections' and 'unallowed_connections' at same time"
                );
            }
            $connectionsListsIntersections = array_intersect($allowedLists, $unallowedLists);
            if(count($connectionsListsIntersections)) {
                $connectionListId = $connectionsListsIntersections[0];
                throw new UnprocessableRequestException(
                    2, "Incorrect structure of '$privacySettingName' param, id '$connectionListId' "
                    . "cannot be in 'allowed_lists' and 'unallowed_lists' at same time"
                );
            }
            Assertion::isArray($allowedConnections,   "Incorrect structure of '$privacySettingName' param, property 'allowed_connections' should be an array");
            Assertion::isArray($unallowedConnections, "Incorrect structure of '$privacySettingName' param, property 'unallowed_connections' should be an array");
            Assertion::isArray($allowedLists,    "Incorrect structure of '$privacySettingName' param, property 'allowed_lists' should be an array");
            Assertion::isArray($unallowedLists,  "Incorrect structure of '$privacySettingName' param, property 'unallowed_lists' should be an array");
        } catch (\Assert\LazyAssertionException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
}