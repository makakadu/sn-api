<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Domain\Model\Users\Photos\Photo;
use App\Domain\Model\Users\AlbumPhoto\AlbumPhoto;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Post\Post as UserPost;
use App\Application\Users\UserPostToDtoTransformer;
use App\Domain\Model\Users\Comments\ProfileComment;


class SavesCollectionItemToDtoVisitor implements \App\Domain\Model\SaveableVisitor {
    
    private \App\Domain\Model\Users\PrivacyService\PrivacyService $privacyService;

    /**
     * @return mixed
     */
    public function visitUserAlbumPhoto(User $requester, AlbumPhoto $photo) {

        $album = $photo->album();
        $accessIsAllowed = true;
        if(!$requester->equals($photo->owner()) && $album->isSoftlyDeleted()) {
            $accessIsAllowed = false;
        }
        elseif(!$this->privacyService->hasAccess($album->whoCanSee(), $requester)) {
            $accessIsAllowed = false;
        }
        
        if($accessIsAllowed) {
            return [
                'type' => 'user_album_photo',
                'id' => $photo->id(),
                'original' => $photo->original()
            ];
        } else {
            return [];
        }
    }
    
    public function visitProfileComment(User $requester, \App\Domain\Model\Users\Comments\ProfileComment $comment) {
        $transformer = new \App\DataTransformer\Users\ProfileCommentToSavedDTOTransformer();
        return $transformer->transformOne($comment);
    }

    /**
     * @return mixed
     */
    public function visitUserAlbumPhoto2(User $requester, AlbumPhoto $photo) {
        if($this->userAlbumPhotosAuth->canSee($requester, $photo)) {
            return [
                'type' => 'user_album_photo',
                'id' => $photo->id(),
                'original' => $photo->original()
            ];
        } else {
            return [];
        }
    }
    
    /**
     * @return mixed
     */
    public function visitPostVideo(\App\Domain\Model\Users\Post\Video\Video $video) {
        return [];
    }

}
