<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Users\User\User;
use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Users\Photos\Photo;
use App\Domain\Model\Users\Privacy;

class PagePhotosAuthorization {
    use AuthorizationTrait;
    
    private PostsAuthorization $postsAuthorization;
    
    function __construct(PostsAuthorization $postsAuthorization) {
        $this->postsAuthorization = $postsAuthorization;
    }
    
    function failIfCannotShare(User $requester, PhotoInterface $photo) {
        $photoAuthVisitor = new PhotoAuthVisitor($requester, $this->privacy, $this);
        $photo->accept($photoAuthVisitor);
    }
    
    function failIfCannotAddToPost(User $requester, PostPhoto $photo) {
        if(!$photo->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot add photo of another user to post");
        }
    }
    
    function failIfCannotAddPhotoToAlbum(User $requester, Album $album): void {
        if($requester->equals($album->creator())) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot add photo to another's user album");
        }
    }
    function failIfCannotSee(User $requester, Photo $photo): void {
        $this->failIfInBlacklist($this->requester, $photo->creator(), "Banned by photo owner"); // 
        $photoAuthVisitor = new PhotoAuthVisitor($requester, $this->privacy, $this->postsAuthorization);
        $photo->accept($photoAuthVisitor);
    }
    
    function failIfGuestsCannotSee(Photo $photo): void {
        if(!$this->privacy->canGuestSeeAlbum($photo->album())) {
            $this->throwPrivacyException();
        }
    }
    
    function failIfCannotSeeUserCommentPhoto(User $requester, UserCommentPhoto $photo) {
        $owner = $photo->creator();
        if($owner->equals($requester)) { return; }
        $this->failIfInBlacklist($requester, $owner, "Banned by photo owner");
        
        $comment = $photo->comment();
        if(!$comment && !$requester->equals($owner)) {
            throw new ForbiddenException(111, "Фото, которое только подготовленное для коммента, но не привязано к нему, доступно только владельцу");
        } else {
            $commentedId = $photo->comment()->commentedId();
            $commented = $this->repository->findById($commentedId);
            $commented->accept(new CommentableAuthVisitor($requester));
        }
    }
    
    function failIfCannotSeeGroupCommentPhoto(User $requester, UserCommentPhoto $photo) {
//        $group = $photo->group();
//        if($group->isMember($requester)) { return; }
//        $this->failIfInBlacklist($requester, $group, "Banned by photo owner");
    }
    
    function failIfCannotSeePageCommentPhoto(User $requester, PageCommentPhoto $photo) {
        $page = $photo->asPage();
        $this->failIfInBlacklist($requester, $page, "Banned in page");
    }
}

class PhotoAuthVisitor {
    use AuthorizationTrait;
    
    private User $requester;
    private PhotosAuthorization $photosAuthorization;
    private ProfileAlbumsAuthorization $albumsAuthorization;
    
    function __construct(User $requester, PrivacyService $privacy, PhotosAuthorization $photosAuthorization) {
        $this->requester = $requester;
        $this->privacy = $privacy;
        $this->photosAuthorization = $photosAuthorization;
    }
    
    function visitUserPicture(Picture $photo) {
        $owner = $photo->creator();
        if($owner->equals($this->requester)) { return; }
        
        $this->failIfInBlacklist($this->requester, $owner, "Banned by picture owner");
        
        if(!$this->privacy->isProfileAccessibleTo($this->requester, $owner)) {
            throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, "Only friends can see");
        }
    }
    
    function visitUserCommentPhoto(UserCommentPhoto $photo) {
        $owner = $photo->creator();
        if($owner->equals($this->requester)) { return; }
        $this->failIfInBlacklist($this->requester, $owner, "Banned by photo owner");
        
        $comment = $photo->comment();
        if(!$comment && !$this->requester->equals($owner)) {
            throw new ForbiddenException(111, "Фото, которое только подготовленное для коммента, но не привязано к нему, доступно только владельцу");
        } else {
            $commentedId = $photo->comment()->commentedId();
            $commented->accept(new CommentableAuthVisitor($this->requester));
        }
    }
    
    function visitUserPostPhoto(UserPostPhoto $photo) {
        $owner = $photo->creator();
        if($owner->equals($this->requester)) {
            return;
        }
        $this->failIfInBlacklist($this->requester, $owner, "Banned by photo owner");
        
        $post = $photo->commentedPost();
        if(!$post && !$this->requester->equals($owner)) {
            throw new ForbiddenException(111, "PostPhoto, которое только подготовленное для поста, но не привязано к посту, доступно только владельцу");
        } else if($post) {
            if(!$post->isPublic() && $this->privacy->areUsersFriends($this->requester, $owner)) {
                throw new ForbiddenException(111, "Post is accessible only for friends");
            }
        }
    }
    function visitUserAlbumPhoto(UserAlbumPhoto $photo) {
        $owner = $photo->creator();
        if($owner->equals($this->requester)) return;
        
        if($owner->inBlackList($this->requester)) {
            throw new ForbiddenException("Banned by photo owner"); 
        }
        
        $this->albumsAuthorization->failIfCannotSee($photo->album());
    }
    function visitPagePicture(GroupPicture $picture) {
        $page = $picture->asPage();

        if($page->inBlackList($this->requester)) {
            throw new ForbiddenException("Banned in page"); 
        }
    }
    function visitGroupPicture(GroupPicture $picture) {
        $group = $picture->group();
        if($group->isMember($this->requester)) {
            return;
        }
        if($group->inBlackList($this->requester)) {
            throw new ForbiddenException("Banned in group"); 
        }
        // 
        if(!$this->privacy->isGroupAccessibleTo($this->requester, $group)) {
            throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, "Only friends can see");
        }
    }
    
}