<?php
declare(strict_types=1);
namespace App\Application;

use App\Domain\Model\Common\Shares\SharedVisitor;

use App\Domain\Model\Common\Shares\SharedUserPhoto;
use App\Domain\Model\Common\Shares\SharedUserVideo;
use App\Domain\Model\Common\Shares\SharedUserPost;

use App\Domain\Model\Common\Shares\SharedGroupPhoto;
use App\Domain\Model\Common\Shares\SharedGroupVideo;
use App\Domain\Model\Common\Shares\SharedGroupPost;

use App\Domain\Model\Common\Shares\SharedPageAlbumPhoto;
use App\Domain\Model\Common\Shares\SharedPageVideo;
use App\Domain\Model\Common\Shares\SharedPagePost;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\PrivacyService\PrivacyService;

class TransformSharedToDtoVisitor implements SharedVisitor {
    
    private User $requester;
    private PrivacyService $privacy;
    
    function __construct(User $requester, PrivacyService $privacy) {
        $this->requester = $requester;
        $this->privacy = $privacy;
    }
    
    public function visitSharedUserPhoto(SharedUserPhoto $sharedPhoto): array {
        $dto = ['type' => 'user_photo'];
        
        $owner = $sharedPhoto->owner();

        if(!$owner || ($owner && $owner->isSoftlyDeleted())) {
            $dto['owner'] = null;
            $dto['status'] = 'not_exist';
            return $dto;
        }
        $ownerPicture = null;
        if($owner->isBlocked() || $owner->isSuspended() || $owner->isDisabled()) {
            $ownerPicture = null;
        } else {
            $ownerPicture = $owner->currentPicture()
                ? $owner->currentPicture()->small() : null;
        }
        $dto['owner'] = [
            'id' => $owner->id(),
            'fullname' => $owner->fullname(),
            'picture' => $ownerPicture
        ];

        $original = $sharedPhoto->original();
        if(!$original) {
            $dto['status'] = 'not_exist';
        } elseif($original->isDeleted() || $original->isDeletedByManager()) {
            $dto['status'] = 'in_recycle bin';
        } elseif($owner->isBlocked() || $owner->isSuspended() || $owner->isDisabled()) {
            $dto['status'] = 'inactive';
        } elseif(!$this->privacy->canSeeAlbum($this->requester, $original->album())) {
            $dto['status'] = 'protected';
        } else {
            $dto['status'] = 'exist';
            $dto['id'] = $original->id();
            $dto['small'] = $original->small();
        }
        return $dto;
    }

    public function visitSharedUserVideo(SharedUserVideo $video): array {
        return [
            'type' => 'user_video',
            'id' => $video->id(),
            'preview' => $video->previewMedium()
        ];
    }

    public function visitSharedGroupPhoto(SharedGroupPhoto $photo) {
        
    }

    public function visitSharedGroupPost(SharedGroupPost $post) {
        
    }

    public function visitSharedGroupVideo(SharedGroupVideo $video) {
        
    }

    public function visitSharedPagePhoto(SharedPageAlbumPhoto $photo) {
        
    }

    public function visitSharedPagePost(SharedPagePost $post) {
        
    }

    public function visitSharedPageVideo(SharedPageVideo $video) {
        
    }

    public function visitSharedUserPost(SharedUserPost $post) {
        
    }

}