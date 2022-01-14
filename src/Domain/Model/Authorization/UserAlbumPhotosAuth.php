<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Albums\Album;
use App\Domain\Model\Users\Photos\AlbumPhoto\AlbumPhoto;
use App\Application\Errors;
use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Users\Photos\Comment\Comment;
use App\Domain\Model\Users\AccessLevels as AL;
use App\Domain\Model\Users\Photos\Reaction;

class UserAlbumPhotosAuth {
    use AuthorizationTrait;
    
    public function __construct(\App\Domain\Model\Users\PrivacyService\PrivacyResolver_new $privacy) {
        $this->privacy = $privacy;
    }
    
    function canSee(User $requester, AlbumPhoto $albumPhoto): bool {
        return true;
    }
    
    function failIfSoftlyDeleted(AlbumPhoto $albumPhoto, string $message): void {
        if($albumPhoto->isSoftlyDeleted()) {
            throw new ForbiddenException(Errors::SOFTLY_DELETED, $message);
        }
    }
    
    /*
     * Проблема этого метода в том, что он подходит только для проверки доступа к альбому. А всё из-за того, что здесь "жестко закодированы" сообщения.
     * Сама логика подходит для минимум трёх случаев: 1) проверить доступ; 2) проверить можно ли поделиться; 3) можно ли сохранить. Я не говорю, что это точно, возможно
     * появится какое-то отличие и эта логика будет подходить только для проверки доступа. Возможно забаненным пользователям можно будет видеть фото, но нельзя будет поделиться
     * им или сохранить его.
     */
    function failIfCannotSee(User $requester, AlbumPhoto $albumPhoto): void {
        /*
         * Также я не знаю стоит ли уточнять причину недоступности, когда фото недоступно из-за того, что владелец этого фото удалён, заморожен и так далее
         */
        $this->failIfUserIsInactive($albumPhoto->owner(), "Access to album photo is prohibited, photo owner is %");
        $this->failIfSoftlyDeleted($albumPhoto, "Access to album photo is prohibited, photo in trash");
        $this->failIfUnaccessibleToUserByPrivacy($requester, $albumPhoto->album(), "Access to album photo is prohibited by album privacy settings");
    }
    
    function failIfCannotSeeAlt(User $requester, AlbumPhoto $albumPhoto): void {
        $this->failIfUnnaccessible($requester, $albumPhoto, "Access to album photo is prohibited");
    }
    
    function failIfCannotShare(User $requester, AlbumPhoto $albumPhoto): void {
        $this->failIfUserIsInactive($albumPhoto->owner(), "Cannot share album photo, owner is %");
        $this->failIfSoftlyDeleted($albumPhoto, "Cannot share album photo, photo in trash");
        $this->failIfUnaccessibleToUserByPrivacy($requester, $albumPhoto->album(), "Cannot share album photo,album photo is protected by album privacy settings");
    }
    
    function failIfCannotShareAlt(User $requester, AlbumPhoto $albumPhoto): void {
        $this->failIfUnnaccessible($requester, $albumPhoto, "Cannot shared album photo");
    }

    function failIfUnnaccessible(User $requester, AlbumPhoto $albumPhoto, string $message): void {
        $photoOwner = $albumPhoto->owner();
        
        $this->failIfUserIsInactive($photoOwner, "$message, photo owner is %");
        //$this->failIfUserIsDeleted($postOwner, "Post owner is deleted");
        $this->failIfSoftlyDeleted($albumPhoto, "photo in trash");
        $this->failIfUnaccessibleToUserByPrivacy($requester, $albumPhoto->album(), "$message, album photo is protected by album privacy settings");
    }
    
    function failIfGuestsCannotSee(AlbumPhoto $albumPhoto): void {
        $photoOwner = $albumPhoto->owner();
        $this->failIfUserIsInactive($photoOwner, "Access to album photo is prohibited, photo owner is %");
        $this->failIfSoftlyDeleted($albumPhoto, "Access to album photo is prohibited, photo in trash");
        
        $album = $albumPhoto->album();
        if($album->whoCanSee()->accessLevel() !== AL::EVERYONE) {
            throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, "Access to album photo is prohibited by album privacy settings");
        }
    }
    
    function failIfCannotEdit(User $requester, AlbumPhoto $albumPhoto): void {
        /* Если владелец фото неактивен, то выполнение не дойдёт сюда. Если фото мягко удалено, то исключение будет выброшено в AlbumPhoto::edit(...), поэтому здесь не нужно
         * делать эти проверки
         */
        
        if(!$albumPhoto->owner()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot edit album photo from another profile");
        }
    }
    
    function failIfCannotDelete(User $requester, AlbumPhoto $photo): void {
        $photoCreator = $photo->owner();
        /* $this->failIfUserIsInactive($photoCreator, "Profile owner is "); Эта проверка не нужна, потому что фото может удалить только владелец и менеджер. Если $requester
         * является владельцем и выполнение дошло сюда, то владелец точно активен, если же $requester является менеджером, то ему позволено удалять фото, даже если владелец
         * неактивен
         * 
         * Также здесь не будет проверки на то, является ли пользователь модератором, потому что это лёгкая проверка и она нужна, если изменяется свойство $isDeletedByGlobalManager,
         * а здесь происходит проверка на то, может ли $requester изменить $isDeleted
         */
        
        if(!$photoCreator->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot softly delete album photo of another user");
        }
    }
    
    function failIfCannotComment(User $requester, AlbumPhoto $albumPhoto): void {
        $photoOwner = $albumPhoto->owner();
        $this->failIfUserIsInactive($photoOwner, "Cannot comment album photo, photo is unaccessible because owner is %");
        
        if(!$photoOwner->equals($requester)) {
            $this->failIfInBlacklist($requester, $photoOwner, "Commenting is forbidden to banned users");
            $this->failIfUnaccessibleToUserByPrivacy($requester, $albumPhoto->album(), "Cannot comment album photo, access to photo is prohibited by album privacy settings");
            
            $album = $albumPhoto->album();
            if(!$this->privacy->hasAccess($requester, $album->whoCanComment())) {
                throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, "Commenting is prohibited by album privacy settings");
            }
        }
    }
    
    function failIfCannotEditComment(User $requester, Comment $comment): void {
        /*
         * Здесь возникает проблема, это (наверное) единственное место, где у PhotoComment запрашивается Photo и у него вызывается метод album(), а метод album() есть только у
         * AlbumPhoto. Что делать в этой ситуации? Album нужен чтобы узнать есть ли доступ к фото. Можно было бы схалтурить и проверить, что это точно AlbumPhoto
         * и уже только потом вызывать метод album(), но всё не так просто, если для изменения коммента будет использоваться путь /album-photo-comments/123, то это будет 
         * неправильно, потому что для ProfilePicture и AlbumPhoto используется общий класс PhotoComment и для него есть только один репозиторий.
         * Если же путь будет /album-photos/123/comments/147, то тогда нужно будет искать коммент в коллекции AlbumPhoto::$comments и не очень понятно где тогда проводтить
         * авторизацию, может быть добавить в AlbumPhoto метод editComment(...), но нужно же проверить настройки приватности альбома, а если делать это в методе
         * AlbumPhoto::editComment(...) не самая лучшая идея и, опять же, делать авторизацию частью доменной сущности - не самая лучшая идея, ведь авторизация довольно сложная и может
         * меняться, я до сих пор не решил как будет выглядеть логика связанная с менеджерами, потому что у двоих менеджеров могут быть разные полномочия.
         * 
         * Есть несколько вариантов решения:
         * 1. Оставить один класс, а в репозитории добавить 2 разных метода getAlbumPhotoCommentById() и getProfilePictureCommentById
         * 2. Искать коммент в объекте класса AlbumPhoto вместо репозитория, но тогда нужно знать ID этого фото.
         * 3. Всунуть авторизацию в AlbumPhoto::editComment(), проблема в том, что авторизация сложная
         */
        
        $photo = $comment->commentedPhoto();
        $photoOwner = $photo->owner();
        
        $this->failIfUserIsInactive($photoOwner, "Access to album photo is prohibited, photo owner is %");
        
        if(!$photoOwner->equals($requester)) {
            $this->failIfInBlacklist($requester, $photoOwner, "Editing comments is prohibited to banned users");
            $this->failIfUnaccessibleToUserByPrivacy($requester, $photo->album(), "Editing comments is prohibited by album privacy settings");
            /* Возможно стоит запретить редачить комменты, если пользователь запретил оставлять комменты в настройках приватности альбома
            if(!$this->privacy->hasAccess($requester, $photo->album()->whoCanComment())) {
                throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, "Editing comments is prohibited by album privacy settings");
            }
             * 
             */
        }
        
        if(!$comment->creator()->equals($requester)) { // Только создатель может отредачить свой коммент
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot edit comment created by another user");
        }
    }
    
    function failIfCannotDeleteComment(User $requester, Comment $comment): void {
        $photo = $comment->commentedPhoto();
        $photoOwner = $photo->owner();
        /*
         * Возможно стоит запретить удалять коммент, если пользователь забанен или если к комменту нет доступа или запрещено комментировать настройками приватности. Я не знаю,
         * мне кажется, что стоит дать возможность пользователю распоряжаться тем, что он создал.
         * Если уж разрешить удалять комменты, то стоит разрешить удалять их даже, если владелец поста неактивен.
         */

        if(!$comment->creator()->equals($requester) && !$photoOwner->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot delete comment created by another user");
        }
    }
    
    function failIfCannotReact(User $requester, AlbumPhoto $albumPhoto): void {
        $photoOwner = $albumPhoto->owner();
        $this->failIfUserIsInactive($photoOwner, "Cannot react to album photo, photo is unaccessible because owner is %");
        $this->failIfInBlacklist($requester, $photoOwner, "Cannot react to album photo created on profile where requester is banned");
        $this->failIfUnaccessibleToUserByPrivacy($requester, $albumPhoto->album(), "Cannot react to photo, access to photo is prohibited by album privacy settings");
    }
    
    function failIfCannotEditReaction(User $requester, Reaction $reaction): void {
        $photo = $reaction->photo();
        $photoOwner = $photo->owner();
        
        $this->failIfUserIsInactive($photoOwner, "Cannot edit reaction to album photo, photo is unaccessible because owner is %");
        $this->failIfUnaccessibleToUserByPrivacy($requester, $photo, "Cannot edit reaction to album photo, access to photo is prohibited by album privacy settings");
        $this->failIfInBlacklist($requester, $photo->owner(), "Cannot edit reaction to album photo created on profile where requester is banned");

        if(!$reaction->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot edit reaction to album photo created by another user");
        }
    }
    
    function failIfCannotDeleteReaction(User $requester, Reaction $reaction): void {
        if(!$reaction->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot delete reaction created by another user");
        }
    }
    
    function failIfCannotReactToComment(User $requester, Comment $comment): void {
        $photo = $comment->commentedPhoto();
        $photoOwner = $photo->owner();
        
        $this->failIfUserIsInactive($photoOwner, "Cannot react to comment from photo, photo is unaccessible because owner is %");
        $this->failIfInBlacklist($requester, $photoOwner, "Cannot react to comment from photo created on profile where requester is banned");
        $this->failIfUnaccessibleToUserByPrivacy($requester, $photo, "Cannot react to comment from photo, access to photo is prohibited by album privacy settings");
    }
    
    function failIfCannotEditCommentReaction(User $requester, Reaction $reaction): void {
        $photo = $reaction->photo();
        $photoOwner = $photo->owner();
        
        $this->failIfUserIsInactive($photoOwner, "Cannot edit reaction to comment from photo, photo is unaccessible because owner is %");
        $this->failIfInBlacklist($requester, $photo->owner(), "Cannot edit reaction to comment from photo created on profile where requester is banned");
        $this->failIfUnaccessibleToUserByPrivacy($requester, $photo, "Cannot edit reaction to comment from photo, access to photo is prohibited by album privacy settings");
        
        if(!$reaction->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot edit reaction created by another user");
        }
    }
    
    function failIfCannotDeleteCommentReaction(User $requester, CommentReaction $reaction): void {
        /* Можно удалять реакцию на коммент, даже если нет доступа к комменту.
         * Но удалить можно ТОЛЬКО свою реакцию, даже менеджер не может удалить чужую реакцию
         * 
         */
        if(!$reaction->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot delete reaction created by another user");
        }
    }
    
    function failIfUnaccessibleToUserByPrivacy(User $requester, Album $album, string $failMessage): void {
        if($album->user()->equals($requester)) { // метод hasAccess() не должен быть вызван, если владелец альбома === $requester
            return;
        }
        if(!$this->privacy->hasAccess($requester, $album->whoCanSee())) {
            throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, $failMessage);
        }
        
    }
}