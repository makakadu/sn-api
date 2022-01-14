<?php
declare(strict_types=1);
namespace App\DataTransformer;

use App\DTO\Shares\SharedDTO;
use App\Domain\Model\Common\Shares\SharedVisitor;
use App\DTO\CreatorDTO;

use App\Domain\Model\Users\User\User;

use App\Domain\Model\Common\Shares\SharedUserAlbumPhoto;
use App\Domain\Model\Common\Shares\SharedProfilePicture;
use App\Domain\Model\Common\Shares\SharedUserVideo;
use App\Domain\Model\Common\Shares\SharedUserPost;

use App\Domain\Model\Common\Shares\SharedGroupAlbumPhoto;
use App\Domain\Model\Common\Shares\SharedGroupPicture;
use App\Domain\Model\Common\Shares\SharedGroupVideo;
use App\Domain\Model\Common\Shares\SharedGroupPost;

use App\Domain\Model\Common\Shares\SharedPageAlbumPhoto;
use App\Domain\Model\Common\Shares\SharedPagePicture;
use App\Domain\Model\Common\Shares\SharedPageVideo;
use App\Domain\Model\Common\Shares\SharedPagePost;

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

use App\DTO\Shares\InaccessibleSharedDTO;
use App\DTO\Shares\SharedUserAlbumPhotoDTO;
use App\DTO\Shares\SharedProfilePictureDTO;
use App\DTO\Shares\SharedUserPostDTO;
use App\DTO\Shares\SharedUserVideoDTO;

use App\DTO\Shares\SharedGroupAlbumPhotoDTO;
use App\DTO\Shares\SharedGroupPictureDTO;
use App\DTO\Shares\SharedGroupPostDTO;
use App\DTO\Shares\SharedGroupVideoDTO;

use App\DTO\Shares\SharedPageAlbumPhotoDTO;
use App\DTO\Shares\SharedPagePictureDTO;
use App\DTO\Shares\SharedPagePostDTO;
use App\DTO\Shares\SharedPageVideoDTO;

use App\DTO\Common\AttachmentDTO;
use App\Domain\Model\Common\Shares\Shared;

/**
 * @implements SharedVisitor<SharedDTO>
 */
class SharedTransformer implements SharedVisitor {
    use \App\DataTransformer\TransformerTrait;
    
    private ?User $requester;
    
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

    public function __construct(UserAlbumPhotosAuth $userAlbumPhotosAuth, UserPostsAuth $userPostsAuth, UserVideosAuth $userVideosAuth, ProfilePicturesAuth $profilePicturesAuth, GroupAlbumPhotosAuth $groupAlbumPhotosAuth, GroupPostsAuth $groupPostsAuth, GroupVideosAuth $groupVideosAuth, GroupPicturesAuth $groupPicturesAuth, PageAlbumPhotosAuth $pageAlbumPhotosAuth, PagePostsAuth $pagePostsAuth, PageVideosAuth $pageVideosAuth, PagePicturesAuth $pagePicturesAuth) {
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
    }

    function transform(?User $requester, Shared $shared): SharedDTO {
        $this->requester = $requester;
        return $shared->acceptSharedVisitor($this);
    }
    
    /**
     * @return SharedGroupAlbumPhotoDTO|InaccessibleSharedDTO
     */
    public function visitSharedGroupAlbumPhoto(SharedGroupAlbumPhoto $shared) {
        $original = $shared->photo();

        $type = 'group_album_photo';
        $creator = $shared->creator();
        $group = $shared->group();
        $timestamp = $this->creationTimeToTimestamp($shared->originalCreatedAt());
        
        
        $onBehalfOfGroup = $shared->onBehalfOfGroup();
        $creatorDTO = $creator && !$onBehalfOfGroup ? $this->creatorToDTO($creator) : null;

        $groupDTO = $group ? $this->groupToSmallDTO($group) : null;

        if($original) {
            $originalIsUnaccessible = false;
            $unaccessabilityReason = "";
            if(($this->requester && !$this->groupAlbumPhotosAuth->canSee($this->requester, $original))
                || (!$this->requester && $this->groupAlbumPhotosAuth->guestsCanSee($original))
            ) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "forbidden";
            }
            if($original->isSoftlyDeleted()) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "softly_deleted";
            }
            if($originalIsUnaccessible) {
                return new UnaccessibleGroupSharedDTO($shared->originalId(), $creatorDTO, $groupDTO, $type, $onBehalfOfGroup, $unaccessabilityReason, $timestamp);
            }
            return new SharedGroupAlbumPhotoDTO(
                $shared->originalId(),
                $creatorDTO,
                $groupDTO,
                $original ? $original->medium() : null,
                $onBehalfOfGroup,
                $timestamp
            );
        } else {
            return new UnaccessibleGroupSharedDTO($shared->originalId(), $creatorDTO, $groupDTO, $type, $onBehalfOfGroup, 'deleted', $timestamp);
        }
        
    }
    
    /**
     * @return SharedGroupPictureDTO|InaccessibleSharedDTO
     */
    public function visitSharedGroupPicture(SharedGroupPicture $shared) {
        $original = $shared->picture();
        
        $type = 'group_picture';
        $group = $shared->group();
        $timestamp = $this->creationTimeToTimestamp($shared->originalCreatedAt());
        
        $onBehalfOfGroup = true;
        $groupDTO = $group ? $this->groupToSmallDTO($group) : null;
        
        if($original) {
            $originalIsUnaccessible = false;
            $unaccessabilityReason = "";
            
            if(($this->requester && !$this->groupPicturesAuth->canSee($this->requester, $original))
                || (!$this->requester && $this->groupPicturesAuth->guestsCanSee($original))
            ) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "forbidden";
            }
            if($original->isSoftlyDeleted()) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "softly_deleted";
            }
            if($originalIsUnaccessible) {
                return new UnaccessibleGroupSharedDTO($shared->originalId(), null, $groupDTO, $type, $onBehalfOfGroup, $unaccessabilityReason, $timestamp);
            }
            return new SharedGroupPictureDTO(
                $shared->originalId(),
                $groupDTO,
                $original ? $original->medium() : null,
                $timestamp
            );
        } else {
            return new UnaccessibleGroupSharedDTO($shared->originalId(), null, $groupDTO, $type, $onBehalfOfGroup, 'deleted', $timestamp);
        }
    }
    
    /**
     * @return SharedGroupPostDTO|InaccessibleSharedDTO
     */
    public function visitSharedGroupPost(SharedGroupPost $shared) {
        $original = $shared->post();

        $type = 'group_post';
        $creator = $shared->creator();
        $group = $shared->group();
        $timestamp = $this->creationTimeToTimestamp($shared->originalCreatedAt());
        
        $onBehalfOfGroup = $shared->onBehalfOfGroup();
        $creatorDTO = $creator && !$onBehalfOfGroup ? $this->creatorToDTO($creator) : null;
        $groupDTO = $group ? $this->groupToSmallDTO($group) : null;

        if($original) {
            $originalIsUnaccessible = false;
            $unaccessabilityReason = "";
            if(($this->requester && !$this->groupPostsAuth->canSee($this->requester, $original))
                || (!$this->requester && $this->groupPostsAuth->guestsCanSee($original))
            ) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "forbidden";
            }
            if($original->isSoftlyDeleted()) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "softly_deleted";
            }
            if($originalIsUnaccessible) {
                return new \App\DTO\Shares\UnaccessibleGroupSharedDTO($shared->originalId(), $creatorDTO, $groupDTO, $type, $onBehalfOfGroup, $unaccessabilityReason, $timestamp);
            }
            
            $attachmentsTransformer = new \App\DataTransformer\Groups\PostAttachmentsTransformer();
            $attachments = $attachmentsTransformer->transform($original->attachments());

            return new SharedGroupPostDTO(
                $shared->originalId(),
                $creatorDTO,
                $groupDTO,
                $original->text(),
                $attachments,
                $onBehalfOfGroup,
                $timestamp
            );
        } else {
            return new UnaccessibleGroupSharedDTO($shared->originalId(), $creatorDTO, $groupDTO, $type, $onBehalfOfGroup, 'deleted', $timestamp);
        }
    }
    
    /**
     * @return SharedGroupVideoDTO|InaccessibleSharedDTO
     */
    public function visitSharedGroupVideo(SharedGroupVideo $shared) {
        $original = $shared->video();

        $type = 'group_video';
        $creator = $shared->creator();
        $group = $shared->group();
        $timestamp = $this->creationTimeToTimestamp($shared->originalCreatedAt());
        
        $onBehalfOfGroup = $shared->onBehalfOfGroup();
        $creatorDTO = $creator && !$onBehalfOfGroup ? $this->creatorToDTO($creator) : null;
        $groupDTO = $group ? $this->groupToSmallDTO($group) : null;

        if($original) {
            $originalIsUnaccessible = false;
            $unaccessabilityReason = "";
            if(($this->requester && !$this->groupVideosAuth->canSee($this->requester, $original))
                || (!$this->requester && $this->groupVideosAuth->guestsCanSee($original))
            ) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "forbidden";
            }
            if($original->isSoftlyDeleted()) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "softly_deleted";
            }
            if($originalIsUnaccessible) {
                return new UnaccessibleGroupSharedDTO($shared->originalId(), $creatorDTO, $groupDTO, $type, $onBehalfOfGroup, $unaccessabilityReason, $timestamp);
            }
            return new \App\DTO\Shares\SharedGroupVideoDTO(
                $shared->originalId(),
                $creatorDTO,
                $groupDTO,
                $original->link(),
                $original->previewMedium(),
                $onBehalfOfGroup,
                $timestamp
            );
        } else {
            return new UnaccessibleGroupSharedDTO($shared->originalId(), $creatorDTO, $groupDTO, $type, $onBehalfOfGroup, 'deleted', $timestamp);
        }
    }
    
    /**
     * @return InaccessibleSharedDTO|SharedPageAlbumPhotoDTO
     */
    public function visitSharedPageAlbumPhoto(SharedPageAlbumPhoto $shared) {
        $original = $shared->photo();

        $type = 'page_album_photo';
        $creator = $shared->creator();
        $page = $shared->page();
        $timestamp = $this->creationTimeToTimestamp($shared->originalCreatedAt());
        
        $onBehalfOfPage = $shared->onBehalfOfPage();
        $creatorDTO = $creator && !$onBehalfOfPage ? $this->creatorToDTO($creator) : null;
        $pageDTO = $page ? $this->pageToSmallDTO($page) : null;

        if($original) {
            $originalIsUnaccessible = false;
            $unaccessabilityReason = "";
            if(!$this->requester && !$this->pageAuth->guestsCanSeeAlbumPhotos()) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "forbidden";
            }
            if($original->isSoftlyDeleted()) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "softly_deleted";
            }
            if($originalIsUnaccessible) {
                return new UnaccessiblePageSharedDTO($shared->originalId(), $creatorDTO, $pageDTO, $type, $onBehalfOfPage, $unaccessabilityReason, $timestamp);
            }
            return new SharedPageAlbumPhotoDTO(
                $shared->originalId(),
                $creatorDTO,
                $pageDTO,
                $original ? $original->medium() : null,
                $onBehalfOfPage,
                $timestamp
            );
        } else {
            return new UnaccessiblePageSharedDTO($shared->originalId(), $creatorDTO, $pageDTO, $type, $onBehalfOfPage, 'deleted', $timestamp);
        }
    }

    /**
     * @return InaccessibleSharedDTO|SharedPagePictureDTO
     */
    public function visitSharedPagePicture(SharedPagePicture $shared) {
        $original = $shared->picture();

        $type = 'page_picture';
        $page = $shared->page();
        $timestamp = $this->creationTimeToTimestamp($shared->originalCreatedAt());
        
        $pageDTO = $page ? $this->pageToSmallDTO($page) : null;

        if($original) {
            $originalIsUnaccessible = false;
            $unaccessabilityReason = "";
            
            if($original->isSoftlyDeleted()) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "softly_deleted";
            }
            if($originalIsUnaccessible) {
                return new UnaccessiblePageSharedDTO($shared->originalId(), null, $pageDTO, $type, true, $unaccessabilityReason, $timestamp);
            }
            return new SharedPagePictureDTO(
                $shared->originalId(),
                $pageDTO,
                $original ? $original->medium() : null,
                $timestamp
            );
        } else {
            return new UnaccessiblePageSharedDTO($shared->originalId(), null, $pageDTO, $type, true, 'deleted', $timestamp);
        }
    }

    /**
     * @return InaccessibleSharedDTO|SharedPagePostDTO
     */
    public function visitSharedPagePost(SharedPagePost $shared) {
        $original = $shared->post();

        $type = 'page_post';
        $page = $shared->page();
        $timestamp = $this->creationTimeToTimestamp($shared->originalCreatedAt());

        $pageDTO = $page && !$page->isDeleted() ? $this->pageToSmallDTO($page) : null;
        
        /** @var ?string $unaccessibilityReason */
        $unaccessibilityReason = null;
        if(!$original || ($original && $original->owningPage()->isDeleted())) {
            $unaccessibilityReason = "deleted";
        } elseif($original && $original->isSoftlyDeleted()) {
            $unaccessibilityReason = "softly_deleted";
        } elseif(!$this->pagePostsAuth->isAccessible($original)) {
            $unaccessibilityReason = "forbidden";
        }
        if($unaccessibilityReason) {
            return new UnaccessiblePageSharedDTO($shared->originalId(), null, $pageDTO, $type, true, $unaccessibilityReason, $timestamp);
        }
        $attachmentsTransformer = new \App\DataTransformer\Pages\PostAttachmentsTransformer();
        
        return new SharedPagePostDTO(
            $shared->originalId(),
            $pageDTO,
            $original->text(),
            $attachmentsTransformer->transform($original->attachments()),
            $timestamp
        );
    }

    /**
     * @return InaccessibleSharedDTO|SharedPageVideoDTO
     */
    public function visitSharedPageVideo(SharedPageVideo $shared) {
        $original = $shared->video();

        $type = 'page_video';
        $creator = $shared->creator();
        $page = $shared->page();
        $timestamp = $this->creationTimeToTimestamp($shared->originalCreatedAt());
        
        $onBehalfOfPage = $shared->onBehalfOfPage();
        $creatorDTO = $creator && !$onBehalfOfPage ? $this->creatorToDTO($creator) : null;
        $pageDTO = $page ? $this->pageToSmallDTO($page) : null;

        if($original) {
            $originalIsUnaccessible = false;
            $unaccessabilityReason = "";

            if($original->isSoftlyDeleted()) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "softly_deleted";
            }
            if($originalIsUnaccessible) {
                return new UnaccessiblePageSharedDTO($shared->originalId(), $creatorDTO, $pageDTO, $type, $onBehalfOfPage, $unaccessabilityReason, $timestamp);
            }
            return new SharedPageVideoDTO(
                $shared->originalId(),
                $creatorDTO,
                $pageDTO,
                $original->link(),
                $original->previewMedium(), 
                $onBehalfOfPage,
                $timestamp
            );
        } else {
            return new UnaccessiblePageSharedDTO($shared->originalId(), $creatorDTO, $pageDTO, $type, $onBehalfOfPage, 'deleted', $timestamp);
        }
    }

    /**
     * @return InaccessibleSharedDTO|SharedUserAlbumPhotoDTO
     */
    public function visitSharedUserAlbumPhoto(SharedUserAlbumPhoto $shared) {
        $original = $shared->photo();

        $type = 'user_album_photo';
        $creator = $shared->creator();
        $timestamp = $this->creationTimeToTimestamp($shared->originalCreatedAt());
        
        $creatorDTO = $this->creatorToDTO($creator);

        if($original) {
            $originalIsUnaccessible = false;
            $unaccessabilityReason = "";
            if(($this->requester && !$this->userAlbumPhotosAuth->canSee($this->requester, $original))
                || (!$this->requester && $this->userAlbumPhotosAuth->guestsCanSee($original))
            ) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "forbidden";
            }
            if($original->isSoftlyDeleted()) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "softly_deleted";
            }
            if($originalIsUnaccessible) {
                return new \App\DTO\Shares\InaccessibleUserSharedDTO($shared->originalId(), $creatorDTO, $type, $unaccessabilityReason, $timestamp);
            }
            return new SharedUserAlbumPhotoDTO(
                $shared->originalId(),
                $creatorDTO,
                $original->medium(),
                $timestamp
            );
        } else {
            return new UnaccessibleUserSharedDTO($shared->originalId(), $creatorDTO, $type, 'deleted', $timestamp);
        }
    }

    /**
     * @return SharedProfilePictureDTO|InaccessibleSharedDTO
     */
    public function visitSharedProfilePicture(SharedProfilePicture $shared) {
        $original = $shared->picture();

        $type = 'profile_picture';
        $creator = $shared->creator();
        $timestamp = $this->creationTimeToTimestamp($shared->originalCreatedAt());
        
        $creatorDTO = $this->creatorToDTO($creator);

        if($original) {
            $originalIsUnaccessible = false;
            $unaccessabilityReason = "";
            if(($this->requester && !$this->userPicturesAuth->canSee($this->requester, $original))
                || (!$this->requester && $this->userPicturesAuth->guestsCanSee($original))
            ) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "forbidden";
            }
            if($original->isSoftlyDeleted()) {
                $originalIsUnaccessible = true;
                $unaccessabilityReason = "softly_deleted";
            }
            if($originalIsUnaccessible) {
                return new \App\DTO\Shares\InaccessibleUserSharedDTO($shared->originalId(), $creatorDTO, $type, $unaccessabilityReason, $timestamp);
            }
            return new SharedProfilePictureDTO(
                $shared->originalId(),
                $creatorDTO,
                $original->medium(),
                $timestamp
            );
        } else {
            return new UnaccessibleUserSharedDTO($shared->originalId(), $creatorDTO, $type, 'deleted', $timestamp);
        }
    }

    /**
     * @return SharedUserPostDTO|InaccessibleSharedDTO
     */
    public function visitSharedUserPost(SharedUserPost $shared) {
        $original = $shared->post();
        $type = 'user_post';
        $creator = $shared->creator();
        $timestamp = $this->creationTimeToTimestamp($shared->originalCreatedAt());
        $creatorDTO = $creator && !$creator->isDeleted() ? $this->creatorToDTO($creator) : null;

        if($original) {
            if($original && $original->owner()->isDeleted()) {
                return new InaccessibleUserSharedDTO($shared->originalId(), $creatorDTO, $type, 'deleted', $timestamp);
            } elseif($original->isSoftlyDeleted()) {
                return new InaccessibleUserSharedDTO($shared->originalId(), $creatorDTO, $type, 'softly_deleted', $timestamp);
            } elseif(($this->requester && !$this->userPostsAuth->canSee($this->requester, $original))
                || (!$this->requester && !$this->userPostsAuth->guestsCanSee($original))
            ) {
                return new InaccessibleUserSharedDTO($shared->originalId(), $creatorDTO, $type, 'forbidden', $timestamp);
            }
            $attachmentsTransformer = new \App\DataTransformer\Users\PostAttachmentsTransformer();
            return new SharedUserPostDTO(
                $creatorDTO,
                $shared->originalId(),
                $original->text(),
                $attachmentsTransformer->transform($original->attachments()),
                $timestamp
            );
        } else {
            return new \App\DTO\Shares\InaccessibleUserSharedDTO($shared->originalId(), $creatorDTO, $type, 'deleted', $timestamp);
        }
    }

    /**
     * @return SharedUserVideoDTO|InaccessibleSharedDTO
     */
    public function visitSharedUserVideo(SharedUserVideo $shared) {
        $original = $shared->video();
        $type = 'user_video';
        $creator = $shared->creator();
        $timestamp = $this->creationTimeToTimestamp($shared->originalCreatedAt());
        $creatorDTO = $creator && !$creator->isDeleted() ? $this->creatorToDTO($creator) : null;

        /** @var ?string $inaccessibilityReason */
        $inaccessibilityReason = null;
        if(!$original || ($original && $original->owner()->isDeleted())) {
            $inaccessibilityReason = "deleted";
        } elseif($original && $original->isSoftlyDeleted()) {
            $inaccessibilityReason = "softly_deleted";
        } elseif(($this->requester && !$this->userVideosAuth->canSee($this->requester, $original))
            || (!$this->requester && !$this->userVideosAuth->guestsCanSee($original))
        ) {
            $inaccessibilityReason = "forbidden";
        }
        if($inaccessibilityReason) {
            return new \App\DTO\Shares\InaccessibleUserSharedDTO($shared->originalId(), $creatorDTO, $type, $inaccessibilityReason, $timestamp);
        }
        return new SharedUserVideoDTO(
            $shared->originalId(),
            $creatorDTO,
            $original->link(),
            $original->previewMedium(),
            $timestamp
        );
    }

}