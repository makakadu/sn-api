<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;


use App\Domain\Model\Users\SavesCollection\ProfileItems\UserAlbumPhotoItem;
use App\Domain\Model\Users\SavesCollection\ProfileItems\ProfilePictureItem;
use App\Domain\Model\Users\SavesCollection\ProfileItems\UserVideoItem;
use App\Domain\Model\Users\SavesCollection\ProfileItems\UserPostItem;

use App\Domain\Model\Users\SavesCollection\GroupItems\GroupAlbumPhotoItem;
use App\Domain\Model\Users\SavesCollection\GroupItems\GroupPictureItem;
use App\Domain\Model\Users\SavesCollection\GroupItems\GroupVideoItem;
use App\Domain\Model\Users\SavesCollection\GroupItems\GroupPostItem;

use App\Domain\Model\Users\SavesCollection\PageItems\PageAlbumPhotoItem;
use App\Domain\Model\Users\SavesCollection\PageItems\PagePictureItem;
use App\Domain\Model\Users\SavesCollection\PageItems\PageVideoItem;
use App\Domain\Model\Users\SavesCollection\PageItems\PagePostItem;

use App\Domain\Model\Users\User\User;
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

use App\DTO\Groups\GroupAlbumPhotoDTO;
use App\DTO\Groups\GroupPictureDTO;
use App\DTO\Groups\GroupPostDTO;
use App\DTO\Groups\GroupVideoDTO;

use App\DTO\Users\UserAlbumPhotoDTO;
use App\DTO\Users\PictureDTO as ProfilePictureDTO;
use App\DTO\Users\UserPostDTO;
use App\DTO\Users\UserVideoDTO;

use App\DTO\Pages\PageAlbumPhotoDTO;
use App\DTO\Pages\PagePictureDTO;
use App\DTO\Pages\PagePostDTO;
use App\DTO\Pages\PageVideoDTO;

use App\DTO\Groups\UnaccessibleGroupAlbumPhotoDTO;
use App\DTO\Groups\UnaccessibleGroupPictureDTO;
use App\DTO\Groups\UnaccessibleGroupPostDTO;
use App\DTO\Groups\UnaccessibleGroupVideoDTO;

use App\DTO\Users\UnaccessibleUserAlbumPhotoDTO;
use App\DTO\Users\UnaccessibleProfilePictureDTO;
use App\DTO\Users\UnaccessibleUserPostDTO;
use App\DTO\Users\UnaccessibleUserVideoDTO;

use App\DTO\Pages\UnaccessiblePageAlbumPhotoDTO;
use App\DTO\Pages\UnaccessiblePagePictureDTO;
use App\DTO\Pages\UnaccessiblePagePostDTO;
use App\DTO\Pages\UnaccessiblePageVideoDTO;

use App\Domain\Model\Users\SavesCollection\SavedItem;

use App\DataTransformer\Users\PostTransformer as UserPostTransformer;
use App\DataTransformer\Groups\PostTransformer as GroupPostTransformer;
use App\DataTransformer\Pages\PostTransformer as PagePostTransformer;

/**
 * @implements \App\Domain\Model\Users\SavesCollection\SavedItemVisitor <DTO>
 */
class SavesTransformer implements \App\Domain\Model\Users\SavesCollection\SavedItemVisitor {
    use \App\DataTransformer\TransformerTrait;
    
    private User $requester;
    // private SaveableAuth $auth;
    
    private UserAlbumPhotosAuth $userAlbumPhotosAuth;
    private UserPostsAuth $userPostsAuth;
    private UserVideosAuth $userVideosAuth;
    private ProfilePicturesAuth $profilePicturesAuth;
    
    private PagePostsAuth $pagePostsAuth;
    private PageVideosAuth $pageVideosAuth;
    private PageAlbumPhotosAuth $pageAlbumPhotosAuth;
    private PagePicturesAuth $pagePicturesAuth;

    private GroupPostsAuth $groupPostsAuth;
    private GroupVideosAuth $groupVideosAuth;
    private GroupAlbumPhotosAuth $groupAlbumPhotosAuth;
    private GroupPicturesAuth $groupPicturesAuth;
    
    private UserPostTransformer $profilePostTransformer;
    private GroupPostTransformer $groupPostTransformer;
    private PagePostTransformer $pagePostTransformer;
    
    private int $commentsCount;
    private string $commentsType;
    private string $commentsOrder;
    
    public function __construct(
        UserAlbumPhotosAuth $userAlbumPhotosAuth, 
        UserPostsAuth $userPostsAuth, 
        UserVideosAuth $userVideosAuth, 
        ProfilePicturesAuth $profilePicturesAuth, 
        PagePostsAuth $pagePostsAuth, 
        PageVideosAuth $pageVideosAuth,
        PageAlbumPhotosAuth $pageAlbumPhotosAuth, 
        PagePicturesAuth $pagePicturesAuth, 
        GroupPostsAuth $groupPostsAuth,
        GroupVideosAuth $groupVideosAuth,
        GroupAlbumPhotosAuth $groupAlbumPhotosAuth,
        GroupPicturesAuth $groupPicturesAuth,
        UserPostTransformer $profilePostTransformer,
        GroupPostTransformer $groupPostTransformer,
        PagePostTransformer $pagePostTransformer
    ) {
        $this->userAlbumPhotosAuth = $userAlbumPhotosAuth;
        $this->userPostsAuth = $userPostsAuth;
        $this->userVideosAuth = $userVideosAuth;
        $this->profilePicturesAuth = $profilePicturesAuth;
        $this->pagePostsAuth = $pagePostsAuth;
        $this->pageVideosAuth = $pageVideosAuth;
        $this->pageAlbumPhotosAuth = $pageAlbumPhotosAuth;
        $this->pagePicturesAuth = $pagePicturesAuth;
        $this->groupPostsAuth = $groupPostsAuth;
        $this->groupVideosAuth = $groupVideosAuth;
        $this->groupAlbumPhotosAuth = $groupAlbumPhotosAuth;
        $this->groupPicturesAuth = $groupPicturesAuth;
        $this->profilePostTransformer = $profilePostTransformer;
        $this->groupPostTransformer = $groupPostTransformer;
        $this->pagePostTransformer = $pagePostTransformer;
    }
    
    /**
     * @param array<int, SavedItem> $items
     * @return array<int, DTO>
     */
    public function transform(array $items, User $requester, int $commentsCount, string $commentsType, string $commentsOrder): array {
        $this->requester = $requester;
        $this->commentsCount = $commentsCount;
        $this->commentsType = $commentsType;
        $this->commentsOrder = $commentsOrder;
        
        /** @var array<int, DTO> $dtos */
        $dtos = [];
        /** @var \App\Domain\Model\Users\SavesCollection\SavedItem $item */
        foreach ($items as $item) {
            $dtos[] = $item->acceptItemVisitor($this);            
        }
        return $dtos;
    }
    
    /**
     * @return GroupAlbumPhotoDTO|UnaccessibleGroupAlbumPhotoDTO
     */
    public function visitGroupAlbumPhotoItem(GroupAlbumPhotoItem $item) {
        $photo = $item->photo();
        
        $creator = $item->creator();
        $group = $item->owningGroup();
        
        
        if(!$photo || ($photo && !$this->groupAlbumPhotosAuth->canSee($this->requester, $photo))) {
            return new UnaccessibleGroupAlbumPhotoDTO(
                $item->photoId(),
                $creator && !$item->onBehalfOfGroup() ? $this->creatorToDTO($creator) : null,
                $group ? $this->groupToSmallDTO($group) : null,
                $item->onBehalfOfGroup(),
                $this->creationTimeToTimestamp($item->originalCreatedAt())
            );
        }
        $transformer = new \App\DataTransformer\Groups\AlbumPhotoTransformer();
        return $transformer->transform($photo, $this->commentsCount, $this->commentsType, $this->commentsOrder);
    }
    
    /**
     * @return GroupPictureDTO|UnaccessibleGroupPictureDTO
     */
    public function visitGroupPictureItem(GroupPictureItem $item) {
        $picture = $item->photo();
        $group = $item->owningGroup();
        
        if(!$picture || ($picture && !$this->groupPicturesAuth->canSee($this->requester, $picture))) {
            return new \App\DTO\Groups\UnaccessibleGroupPictureDTO(
                $item->pictureId(),
                $group ? $this->groupToSmallDTO($group) : null,
                $this->creationTimeToTimestamp($item->originalCreatedAt())
            );
        }
        
        $transformer = new \App\DataTransformer\Groups\PictureTransformer();
        return $transformer->transform($picture, $this->commentsCount, $this->commentsType, $this->commentsOrder);
    }
    
    /**
     * @return GroupPostDTO|UnaccessibleGroupPostDTO
     */
    public function visitGroupPostItem(GroupPostItem $item) {
        $post = $item->photo();
        
        $creator = $item->creator();
        $group = $item->owningGroup();
        
        if(!$post || ($post && !$this->groupPostsAuth->canSee($this->requester, $post))) {
            return new \App\DTO\Groups\UnaccessibleGroupPostDTO(
                $item->postId(),
                $creator && !$item->onBehalfOfGroup() ? $this->creatorToDTO($creator) : null,
                $group ? $this->groupToSmallDTO($group) : null,
                $item->onBehalfOfGroup(),
                $this->creationTimeToTimestamp($item->originalCreatedAt())
            );
        }

        return $this->groupPostTransformer->transform($this->requester, $post, $this->commentsCount, $this->commentsType, $this->commentsOrder);
    }

    /**
     * @return GroupVideoDTO|UnaccessibleGroupVideoDTO
     */
    public function visitGroupVideoItem(GroupVideoItem $item) {
        $video = $item->video();
        
        $creator = $item->creator();
        $group = $item->owningGroup();
        
        if(!$video || ($video && !$this->groupVideosAuth->canSee($this->requester, $video))) {
            return new \App\DTO\Groups\UnaccessibleGroupVideoDTO(
                $item->videoId(),
                $creator && !$item->onBehalfOfGroup() ? $this->creatorToDTO($creator) : null,
                $group ? $this->groupToSmallDTO($group) : null,
                $item->onBehalfOfGroup(),
                $this->creationTimeToTimestamp($item->originalCreatedAt())
            );
        }
        
        $transformer = new \App\DataTransformer\Groups\VideoTransformer();
        return $transformer->transform($video, $this->commentsCount, $this->commentsType, $this->commentsOrder);
    }
    
    /**
     * @return PageAlbumPhotoDTO|UnaccessiblePageAlbumPhotoDTO
     */
    public function visitPageAlbumPhotoItem(PageAlbumPhotoItem $item) {
        $photo = $item->photo();
        
        $creator = $item->creator();
        $page = $item->owningPage();
        
        if(!$photo || ($photo && !$this->pageAlbumPhotosAuth->canSee($this->requester, $photo))) {
            return new \App\DTO\Pages\UnaccessiblePageAlbumPhotoDTO(
                $item->photoId(),
                $creator && !$item->onBehalfOfPage() ? $this->creatorToDTO($creator) : null,
                $page ? $this->pageToSmallDTO($page) : null,
                $item->onBehalfOfPage(),
                $this->creationTimeToTimestamp($item->originalCreatedAt())
            );
        }
        
        $transformer = new \App\DataTransformer\Pages\AlbumPhotoTransformer();
        return $transformer->transform($photo, $this->commentsCount, $this->commentsType, $this->commentsOrder);
    }

    /**
     * @return PagePictureDTO|UnaccessiblePagePictureDTO
     */
    public function visitPagePictureItem(PagePictureItem $item) {
        $picture = $item->picture();
        
        $page = $item->owningPage();
        
        if(!$picture || ($picture && !$this->pagePicturesAuth->canSee($this->requester, $picture))) {
            return new \App\DTO\Pages\UnaccessiblePagePictureDTO(
                $item->pictureId(),
                $page ? $this->pageToSmallDTO($page) : null,
                $this->creationTimeToTimestamp($item->originalCreatedAt())
            );
        }
        
        $transformer = new \App\DataTransformer\Pages\PictureTransformer();
        return $transformer->transform($picture, $this->commentsCount, $this->commentsType, $this->commentsOrder);
    }
    
    /**
     * @return PagePostDTO|UnaccessiblePagePostDTO
     */
    public function visitPagePostItem(PagePostItem $item) {
        $post = $item->post();
        
        $creator = $item->creator();
        $page = $item->owningPage();
        
        if(!$post || ($post && !$this->pagePostsAuth->canSee($this->requester, $post))) {
            return new \App\DTO\Pages\UnaccessiblePagePostDTO(
                $item->postId(),
                $creator && $item->showCreator() ? $this->creatorToDTO($creator) : null,
                $page ? $this->pageToSmallDTO($page) : null,
                $this->creationTimeToTimestamp($item->originalCreatedAt())
            );
        }
        return $this->pagePostTransformer->transformOne($this->requester, $post, $this->commentsCount, $this->commentsType, $this->commentsOrder);
    }
    
    /**
     * @return PageVideoDTO|UnaccessiblePageVideoDTO
     */
    public function visitPageVideoItem(PageVideoItem $item) {
        $video = $item->video();
        
        $creator = $item->creator();
        $page = $item->owningPage();
        
        if(!$video || ($video && !$this->pageVideosAuth->canSee($this->requester, $video))) {
            return new \App\DTO\Pages\UnaccessiblePageVideoDTO(
                $item->videoId(),
                $creator && !$item->onBehalfOfPage() ? $this->creatorToDTO($creator) : null,
                $page ? $this->pageToSmallDTO($page) : null,
                $item->onBehalfOfPage(),
                $this->creationTimeToTimestamp($item->originalCreatedAt())
            );
        }
        
        $transformer = new \App\DataTransformer\Pages\VideoTransformer();
        return $transformer->transform($video, $this->commentsCount, $this->commentsType, $this->commentsOrder);
    }
    
    /**
     * @return ProfilePictureDTO|UnaccessibleProfilePictureDTO
     */
    public function visitProfilePictureItem(ProfilePictureItem $item) {
        $picture = $item->picture();
        $creator = $item->owner();
        
        if(!$picture || ($picture && !$this->profilePicturesAuth->canSee($this->requester, $picture))) {
            return new \App\DTO\Users\UnaccessibleProfilePictureDTO(
                $item->pictureId(),
                $creator ? $this->creatorToDTO($creator) : null,
                $this->creationTimeToTimestamp($item->originalCreatedAt())
            );
        }
        $transformer = new \App\DataTransformer\Users\PictureTransformer();
        return $transformer->transform($picture, $this->commentsCount, $this->commentsType, $this->commentsOrder);
    }

    /**
     * @return UserAlbumPhotoDTO|UnaccessibleUserAlbumPhotoDTO
     */
    public function visitUserAlbumPhotoItem(UserAlbumPhotoItem $item) {
        $photo = $item->photo();
        $creator = $item->creator();
        
        if(!$photo || ($photo && !$this->userAlbumPhotosAuth->canSee($this->requester, $photo))) {
            return new \App\DTO\Users\UnaccessibleUserAlbumPhotoDTO(
                $item->photoId(),
                $creator ? $this->creatorToDTO($creator) : null,
                $this->creationTimeToTimestamp($item->originalCreatedAt())
            );
        }

        $transformer = new \App\DataTransformer\Users\AlbumPhotoTransformer();
        return $transformer->transform($photo, $this->commentsCount, $this->commentsType, $this->commentsOrder);
    }

    /**
     * @return UserPostDTO|UnaccessibleUserPostDTO
     */
    public function visitUserPostItem(UserPostItem $item) {
        $post = $item->post();
        $creator = $item->creator();
        
        if(!$post || ($post && !$this->userPostsAuth->canSee($this->requester, $post))) {
            return new \App\DTO\Users\UnaccessibleUserPostDTO(
                $item->postId(),
                $creator ? $this->creatorToDTO($creator) : null,
                $this->creationTimeToTimestamp($item->originalCreatedAt())
            );
        }
        return $this->profilePostTransformer->transformOne($this->requester, $post, $this->commentsCount, $this->commentsType, $this->commentsOrder);
    }
    
    /**
     * @return UserVideoDTO|UnaccessibleUserVideoDTO
     */
    public function visitUserVideoItem(UserVideoItem $item) {
        $video = $item->video();
        $creator = $item->creator();
        
        if(!$video || ($video && !$this->userVideosAuth->canSee($this->requester, $video))) {
            return new \App\DTO\Users\UnaccessibleUserVideoDTO(
                $item->videoId(),
                $creator ? $this->creatorToDTO($creator) : null,
                $this->creationTimeToTimestamp($item->originalCreatedAt())
            );
        }
        
        $transformer = new \App\DataTransformer\Users\VideoTransformer();
        return $transformer->transform($video, $this->commentsCount, $this->commentsType, $this->commentsOrder);
    }
}