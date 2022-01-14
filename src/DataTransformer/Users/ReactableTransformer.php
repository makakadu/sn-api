<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\DTO\CreatorDTO;
use App\Domain\Model\SaveableVisitor;
use App\Domain\Model\Users\SavesCollection\SavedItem;
use App\Domain\Model\Authorization\SaveableAuth;

use App\Domain\Model\Users\Post\Post as UserPost;
use App\Domain\Model\Users\Photos\AlbumPhoto\AlbumPhoto as UserAlbumPhoto;
use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePicture;
use App\Domain\Model\Users\Videos\Video as UserVideo;

use App\Domain\Model\Groups\Post\Post as GroupPost;
use App\Domain\Model\Groups\Photos\AlbumPhoto\AlbumPhoto as GroupAlbumPhoto;
use App\Domain\Model\Groups\Photos\GroupPicture\GroupPicture;
use App\Domain\Model\Groups\Videos\Video as GroupVideo;

use App\Domain\Model\Pages\Post\Post as PagePost;
use App\Domain\Model\Pages\Photos\AlbumPhoto\AlbumPhoto as PageAlbumPhoto;
use App\Domain\Model\Pages\Photos\PagePicture\PagePicture;
use App\Domain\Model\Pages\Videos\Video as PageVideo;

use App\DTO\Common\UnaccessiblePhotoDTO;
use App\DTO\Common\UnaccessibleVideoDTO;
use App\DTO\Common\UnaccessiblePostDTO;

use App\Domain\Model\Users\User\User;

use App\DTO\Common\PostDTO;
use App\DTO\Common\PhotoDTO;
use App\DTO\Common\VideoDTO;

use App\DataTransformer\Users\AlbumPhotoTransformer as UserAlbumPhotoTransformer;
use App\DataTransformer\Users\PictureTransformer as ProfilePictureTransformer;
use App\DataTransformer\Users\VideoTransformer as UserVideoTransformer;
use App\DataTransformer\Users\PostTransformer as UserPostTransformer;

use App\DataTransformer\Groups\AlbumPhotoTransformer as GroupAlbumPhotoTransformer;
use App\DataTransformer\Groups\PictureTransformer as GroupPictureTransformer;
use App\DataTransformer\Groups\VideoTransformer as GroupVideoTransformer;
use App\DataTransformer\Groups\PostTransformer as GroupPostTransformer;

use App\DataTransformer\Pages\AlbumPhotoTransformer as PageAlbumPhotoTransformer;
use App\DataTransformer\Pages\PictureTransformer as PagePictureTransformer;
use App\DataTransformer\Pages\VideoTransformer as PageVideoTransformer;
use App\DataTransformer\Pages\PostTransformer as PagePostTransformer;

use App\Domain\Model\Common\ReactableVisitor;

use App\DataTransformer\Users\ReactedGroupCommentTransformer;
use App\DataTransformer\Users\ReactedPageCommentTransformer;
use App\DataTransformer\Users\ReactedProfileCommentTransformer;

use App\DTO\Common\DTO;

use App\Domain\Model\Authorization\UserAlbumPhotosAuth;
use App\Domain\Model\Authorization\ProfilePicturesAuth;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Authorization\UserVideosAuth;

use App\Domain\Model\Authorization\PagePostsAuth;
use App\Domain\Model\Authorization\PageVideosAuth;
use App\Domain\Model\Authorization\PageAlbumPhotosAuth;
use App\Domain\Model\Authorization\PagePicturesAuth;

use App\Domain\Model\Authorization\GroupPostsAuth;
use App\Domain\Model\Authorization\GroupVideosAuth;
use App\Domain\Model\Authorization\GroupAlbumPhotosAuth;
use App\Domain\Model\Authorization\GroupPicturesAuth;

use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Common\Reaction;

/**
 * @implements ReactableVisitor <DTO>
 */
class ReactableTransformer implements ReactableVisitor {
    use \App\DataTransformer\TransformerTrait;
    // private SaveableAuth $auth;
    
    private UserAlbumPhotosAuth $userAlbumPhotosAuth;
    private UserPostsAuth $userPostsAuth;
    private UserVideosAuth $userVideosAuth;
    private ProfilePicturesAuth $profilePicturesAuth;
    
    private GroupAlbumPhotosAuth $groupAlbumPhotosAuth;
    private GroupPostsAuth $groupPostsAuth;
    private GroupVideosAuth $groupVideosAuth;
    private GroupPicturesAuth $groupPicturesAuth;
    
    private PageAlbumPhotosAuth $pageAlbumPhotosAuth;
    private PagePostsAuth $pagePostsAuth;
    private PageVideosAuth $pageVideosAuth;
    private PagePicturesAuth $pagePicturesAuth;
    
    private ReactedGroupCommentTransformer $groupCommentTransformer;
    private ReactedPageCommentTransformer $pageCommentTransformer;
    private ReactedProfileCommentTransformer $profileCommentTransformer;
    
    private User $requester;
    private int $commentsCount;
    private string $commentsType;
    private string $commentsOrder;
    
    private UserPostTransformer $userPostTransformer;
    private GroupPostTransformer $groupPostTransformer;
    private PagePostTransformer $pagePostTransformer;
    
    public function __construct(
        UserAlbumPhotosAuth $userAlbumPhotosAuth, 
        UserPostsAuth $userPostsAuth, 
        UserVideosAuth $userVideosAuth, 
        ProfilePicturesAuth $profilePicturesAuth, 
        GroupAlbumPhotosAuth $groupAlbumPhotosAuth, 
        GroupPostsAuth $groupPostsAuth, 
        GroupVideosAuth $groupVideosAuth, 
        GroupPicturesAuth $groupPicturesAuth, 
        PageAlbumPhotosAuth $pageAlbumPhotosAuth, 
        PagePostsAuth $pagePostsAuth, 
        PageVideosAuth $pageVideosAuth, 
        PagePicturesAuth $pagePicturesAuth, 
        ReactedGroupCommentTransformer $groupCommentTransformer, 
        ReactedPageCommentTransformer $pageCommentTransformer, 
        ReactedProfileCommentTransformer $profileCommentTransformer,
        UserPostTransformer $userPostTransformer,
        GroupPostTransformer $groupPostTransformer,
        PagePostTransformer $pagePostTransformer
    ) {
        $this->userAlbumPhotosAuth = $userAlbumPhotosAuth;
        $this->userPostsAuth = $userPostsAuth;
        $this->userVideosAuth = $userVideosAuth;
        $this->profilePicturesAuth = $profilePicturesAuth;
        $this->groupAlbumPhotosAuth = $groupAlbumPhotosAuth;
        $this->groupPostsAuth = $groupPostsAuth;
        $this->groupVideosAuth = $groupVideosAuth;
        $this->groupPicturesAuth = $groupPicturesAuth;
        $this->pageAlbumPhotosAuth = $pageAlbumPhotosAuth;
        $this->pagePostsAuth = $pagePostsAuth;
        $this->pageVideosAuth = $pageVideosAuth;
        $this->pagePicturesAuth = $pagePicturesAuth;
        $this->groupCommentTransformer = $groupCommentTransformer;
        $this->pageCommentTransformer = $pageCommentTransformer;
        $this->profileCommentTransformer = $profileCommentTransformer;
        $this->userPostTransformer = $userPostTransformer;
        $this->groupPostTransformer = $groupPostTransformer;
        $this->pagePostTransformer = $pagePostTransformer;
    }

    // Здесь будет передаваться один объект, который реализует Reactable. То есть передаём сущность, а не оболочку Reaction, нет смысла передавать Reaction, потому что
    // если сущность удаляется из БД, то удаляются все реакции к ней, поэтому не может быть такого, что Reaction существует, а сущность нет
    
    /**
     * @param array<int,Reaction> $reactions
     * @return array<int,DTO>
     */
    public function transform(array $reactions, User $requester, int $commentsCount, string $commentsType, string $commentsOrder): array {
        $this->requester = $requester;
        $this->commentsCount = $commentsCount;
        $this->commentsType = $commentsType;
        $this->commentsOrder = $commentsOrder;
        
        /** @var array<int,DTO> $dtos */
        $dtos = [];
        foreach ($reactions as $reaction) {
            $dtos[] = $reaction->reacted()->acceptReactableVisitor($this);
        }
        return $dtos;
    }
    
    /**
     * @return \App\DTO\Groups\UnaccessibleGroupAlbumPhotoDTO|\App\DTO\Groups\GroupAlbumPhotoDTO
     */
    public function visitGroupAlbumPhoto(GroupAlbumPhoto $photo) {
        if(!$this->groupAlbumPhotosAuth->canSee($this->requester, $photo)) {
            return new \App\DTO\Groups\UnaccessibleGroupAlbumPhotoDTO(
                $photo->id(),
                $photo->onBehalfOfGroup() ? null : $this->creatorToDTO($photo->creator()),
                $this->groupToSmallDTO($photo->owningGroup()),
                $photo->onBehalfOfGroup(),
                $this->creationTimeToTimestamp($photo->createdAt())
            );
        }
        return (new GroupAlbumPhotoTransformer())->transform(
            $photo, $this->commentsCount, $this->commentsType, $this->commentsOrder,
        );
    }
    
    /**
     * @return \App\DTO\Groups\UnaccessibleGroupPictureDTO|\App\DTO\Groups\GroupPictureDTO
     */
    public function visitGroupPicture(GroupPicture $picture) {
        if(!$this->groupPicturesAuth->canSee($this->requester, $picture)) {
            return new \App\DTO\Groups\UnaccessibleGroupPictureDTO(
                $picture->id(),
                $this->groupToSmallDTO($picture->owningGroup()),
                $this->creationTimeToTimestamp($picture->createdAt())
            );
        }
        return (new GroupPictureTransformer())->transform(
            $picture, $this->commentsCount, $this->commentsType, $this->commentsOrder,
        );
    }
    
    /**
     * @return \App\DTO\Groups\UnaccessibleGroupPostDTO|\App\DTO\Groups\GroupPostDTO
     */
    public function visitGroupPost(GroupPost $post) {
        if(!$this->groupPostsAuth->canSee($this->requester, $post)) {
            return new \App\DTO\Groups\UnaccessibleGroupPostDTO(
                $post->id(),
                $post->onBehalfOfGroup() && !$post->showCreator() ? null : $this->creatorToDTO($post->creator()),
                $this->groupToSmallDTO($post->owningGroup()),
                $post->onBehalfOfGroup(),
                $this->creationTimeToTimestamp($post->createdAt())
            );
        }
        return $this->groupPostTransformer->transform(
            $this->requester, $post, $this->commentsCount, $this->commentsType, $this->commentsOrder,
        );
    }
    
    /**
     * @return \App\DTO\Groups\UnaccessibleGroupVideoDTO|\App\DTO\Groups\GroupVideoDTO
     */
    public function visitGroupVideo(GroupVideo $video) {
        if(!$this->groupVideosAuth->canSee($this->requester, $video)) {
            return new \App\DTO\Groups\UnaccessibleGroupVideoDTO(
                $video->id(),
                $video->onBehalfOfGroup() ? null : $this->creatorToDTO($video->creator()),
                $this->groupToSmallDTO($video->owningGroup()),
                $video->onBehalfOfGroup(),
                $this->creationTimeToTimestamp($video->createdAt())
            );
        }
        return (new GroupVideoTransformer())->transform(
            $video, $this->commentsCount, $this->commentsType, $this->commentsOrder,
        );
    }
    
    /**
     * 
     */
    public function visitGroupComment(\App\Domain\Model\Groups\Comments\GroupComment $comment) {
        return $this->groupCommentTransformer->transform($comment, $this->requester);
    }

    /**
     * @return \App\DTO\Pages\UnaccessiblePageAlbumPhotoDTO|\App\DTO\Pages\PageAlbumPhotoDTO
     */
    public function visitPageAlbumPhoto(PageAlbumPhoto $photo) {
        if(!$this->pageAlbumPhotosAuth->canSee($this->requester, $photo)) {
            return new \App\DTO\Pages\UnaccessiblePageAlbumPhotoDTO(
                $photo->id(),
                $photo->onBehalfOfPage() ? null : $this->creatorToDTO($photo->creator()),
                $this->pageToSmallDTO($photo->owningPage()),
                $photo->onBehalfOfPage(),
                $this->creationTimeToTimestamp($photo->createdAt())
            );
        }
        return (new PageAlbumPhotoTransformer())->transform(
            $photo, $this->commentsCount, $this->commentsType, $this->commentsOrder,
        );
    }
    
    /**
     * @return \App\DTO\Pages\UnaccessiblePagePictureDTO|\App\DTO\Pages\PagePictureDTO
     */
    public function visitPagePicture(PagePicture $picture) {
        if(!$this->pagePicturesAuth->canSee($this->requester, $picture)) {
            return new \App\DTO\Pages\UnaccessiblePagePictureDTO(
                $picture->id(),
                $this->pageToSmallDTO($picture->owningPage()),
                $this->creationTimeToTimestamp($picture->createdAt())
            );
        }

        return (new PagePictureTransformer())->transform(
            $picture, $this->commentsCount, $this->commentsType, $this->commentsOrder,
        );
    }
    
    /**
     * @return \App\DTO\Pages\UnaccessiblePagePostDTO|\App\DTO\Pages\PagePostDTO
     */
    public function visitPagePost(PagePost $post) {
        if(!$this->pagePostsAuth->canSee($this->requester, $post)) {
            return new \App\DTO\Pages\UnaccessiblePagePostDTO(
                $post->id(),
                !$post->showCreator() ? null : $this->creatorToDTO($post->creator()),
                $this->pageToSmallDTO($post->owningPage()),
                $this->creationTimeToTimestamp($post->createdAt())
            );
        }
        return $this->pagePostTransformer->transformOne(
            $this->requester, $post, $this->commentsCount, $this->commentsType, $this->commentsOrder,
        );
    }
    
    /**
     * @return \App\DTO\Pages\UnaccessiblePageVideoDTO|\App\DTO\Pages\PageVideoDTO
     */
    public function visitPageVideo(PageVideo $video) {
        if(!$this->pageVideosAuth->canSee($this->requester, $video)) {
            return new \App\DTO\Pages\UnaccessiblePageVideoDTO(
                $video->id(),
                $video->onBehalfOfPage() ? null : $this->creatorToDTO($video->creator()),
                $this->pageToSmallDTO($video->owningPage()),
                $video->onBehalfOfPage(),
                $this->creationTimeToTimestamp($video->createdAt())
            );
        }
        return (new PageVideoTransformer())->transform(
            $video, $this->commentsCount, $this->commentsType, $this->commentsOrder,
        );
    }
    
    /**
     * @return \App\DTO\Users\UnaccessibleUserAlbumPhotoDTO|\App\DTO\Users\UserAlbumPhotoDTO
     */
    public function visitUserAlbumPhoto(UserAlbumPhoto $photo) {
        if(!$this->userAlbumPhotosAuth->canSee($this->requester, $photo)) {
            return new \App\DTO\Users\UnaccessibleUserAlbumPhotoDTO(
                $photo->id(),
                $this->creatorToDTO($photo->owner()),
                $this->creationTimeToTimestamp($photo->createdAt())
            );
        }
        return (new UserAlbumPhotoTransformer())->transform(
            $photo, $this->commentsCount, $this->commentsType, $this->commentsOrder,
        );
    }
    
    /**
     * @return \App\DTO\Users\UnaccessibleProfilePictureDTO|\App\DTO\Users\PictureDTO
     */
    public function visitProfilePicture(ProfilePicture $picture) {
        if(!$this->profilePicturesAuth->canSee($this->requester, $picture)) {
            return new \App\DTO\Users\UnaccessibleProfilePictureDTO(
                $picture->id(),
                $this->creatorToDTO($picture->owner()),
                $this->creationTimeToTimestamp($picture->createdAt())
            );
        }
        return (new ProfilePictureTransformer())->transform(
            $picture, $this->commentsCount, $this->commentsType, $this->commentsOrder,
        );
    }
    
    /**
     * @return \App\DTO\Users\UnaccessibleUserPostDTO|\App\DTO\Users\UserPostDTO
     */
    public function visitUserPost(UserPost $post) {
        if(!$this->userPostsAuth->canSee($this->requester, $post)) {
            return new \App\DTO\Users\UnaccessibleUserPostDTO(
                $post->id(),
                $this->creatorToDTO($post->creator()),
                $this->creationTimeToTimestamp($post->createdAt())
            );
        }
        return $this->userPostTransformer->transformOne(
            $this->requester, $post, $this->commentsCount, $this->commentsType, $this->commentsOrder,
        );
    }
    
    /**
     * @return \App\DTO\Users\UnaccessibleUserVideoDTO|\App\DTO\Users\UserVideoDTO
     */
    public function visitUserVideo(UserVideo $video) {
        if(!$this->userVideosAuth->canSee($this->requester, $video)) {
            return new \App\DTO\Users\UnaccessibleUserVideoDTO(
                $video->id(),
                $this->creatorToDTO($video->creator()),
                $this->creationTimeToTimestamp($video->createdAt())
            );
        }
        return (new UserVideoTransformer())->transform(
            $video, $this->commentsCount, $this->commentsType, $this->commentsOrder,
        );
    }

    public function visitPageComment(\App\Domain\Model\Pages\Comments\PageComment $comment) {
        return $this->pageCommentTransformer->transform($comment, $this->requester);
    }

    public function visitProfileComment(\App\Domain\Model\Users\Comments\ProfileComment $comment) {
        return $this->profileCommentTransformer->transform($comment, $this->requester);
    }

}