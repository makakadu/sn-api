<?php
declare(strict_types=1);
namespace App\Application;

use App\Domain\Model\Users\Photos\PhotoRepository;
use App\Domain\Model\Users\User\UserRepository;

abstract class PhotoAppService implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    protected PhotoRepository $photos;

    function __construct(PhotoRepository $photos, UserRepository $users) {
        $this->photos = $photos;
        $this->users = $users;
    }

    function findPhotoOrFail(string $photoId, bool $asTarget): ?PhotoInterface {
        $photo = $this->photos->getById($photoId);
        
        $found = true;
        if(!$photo) {
            $found = false;
        } else {
            $checkPhotoIsActiveVisitor = CheckPhotoIsActiveVisitor();
            $found = $photo->accept($checkPhotoIsActiveVisitor);
        }
        
        if(!$found && $asTarget) {
            throw new NotExistException("Photo $photoId not found");
        } elseif(!$found && !$asTarget) {
            throw new UnprocessableRequestException("Photo $photoId not found");
        }

        return $photo;
    }
    
    function findPhotoOrFailAlt(string $id, bool $asTarget) {
        $photo = $this->photos->getById($id);
        
        $found = true;
        
        if(!$photo || ($photo && $photo->isSoftlyDeleted())) {
            $found = false;
        }
        
        if($photo->commentId()) {
            $comment = $this->comments->getById($commentId);
            $commented = $comment->commentedPost();
            $commentedCreator = $commented->creator();
            
            if($comment->isSoftlyDeleted() || $commented->isSoftlyDeleted() || $commentedCreator->isSoftlyDeleted()) {
                $found = false;
            }
        } elseif($photo->post()) {
            $post = $photo->post();
            if($post && ($post->isSoftlyDeleted() || $post->creator()->isSoftlyDeleted())) {
                $found = false;
            }
        } elseif($photo->album()) {
            $album = $photo->album();
            if($album && ($album->isSoftlyDeleted() || $album->creator()->isSoftlyDeleted())) {
                $found = false;
            }
        } elseif($photo instanceof UserPicture) {
            
        }  
    }
}