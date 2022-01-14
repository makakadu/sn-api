<?php
declare(strict_types=1);
namespace App\Domain\Model\Users;

use App\Application\Exceptions\UnprocessableRequestException;
use App\Domain\Model\Users\Post\Post;
//use App\Domain\Model\Users\Photos\Photo as UserPhoto;
//use App\Domain\Model\Users\Videos\Video as UserVideo;
use App\Domain\Model\Users\Videos\VideoRepository;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Comments\Photo\Photo as CommentPhoto;
use App\Domain\Model\Users\Comments\Video\Video as CommentVideo;
use App\Domain\Model\Users\Post\Photo\Photo as PostPhoto;
use App\Domain\Model\Users\Post\Photo\PhotoRepository as PostPhotoRepository;
use App\Domain\Model\TempPhoto\TempPhoto;
use App\Domain\Model\Users\Comments\Photo\PhotoRepository as CommentPhotoRepository;
use App\Domain\Model\Common\PhotoService;
use App\Application\Exceptions\ValidationException;
use App\Domain\Model\Users\Post\Attachment as UserPostAttachment;

class AttachmentsService {
    //private VideoRepository $videos;
    private PhotoService $photoService;
    private PostPhotoRepository $userPostPhotos;
    private CommentPhotoRepository $userCommentPhotos;

    function __construct(
        PhotoService $photoService,
        PostPhotoRepository $userPostPhotos,
        CommentPhotoRepository $userCommentPhotos
    ) {
        $this->photoService = $photoService;
        $this->userPostPhotos = $userPostPhotos;
        $this->userCommentPhotos = $userCommentPhotos;
    }
//    
//    function createPostPhotoFromTempPhoto(User $requester, string $photoId): PostPhoto {
//        $tempPhoto = $requester->getTempPhoto($photoId);
////        if(!$tempPhoto) {
////            throw new UnprocessableRequestException(123, "'Temp photo' $tempPhotoId not found");
////        }
//        if(!$tempPhoto->creator()->equals($requester)) {
//            throw new UnprocessableRequestException(123, "No access to 'temp photo' $tempPhotoId");
//        }
////        if($tempPhoto->isDeleted()) { // Возможно не стоит разрешать "перемещать в корзину" временное фото, а сразу удалять его
////            throw new UnprocessableRequestException(123, "'Temp photo' $tempPhotoId in trash");
////        }
//        
//        return $requester->createPostPhoto($this->photoService->createPhotoVersionsBasedOnPhoto($tempPhoto));
//    }
    
    /**
     * @param array<mixed> $attachment
     */
    function validatePostAttachment(array $attachment): void {
        if(!isset($attachment['type'])) {
            throw new ValidationException("Param 'attachment' should contain property 'type'");
        }
        elseif(!isset($attachment['id'])) {
            throw new ValidationException("Param 'attachment' should contain property 'id'");
        }
    }
    
//    /** @param array<array> $newAttachmentsList */
//    function removeOldAttachmentsFromPost(Post $post, array $newAttachmentsList): void {
//        $currentAttachmentsList = $post->attachments();
//        
//        foreach ($currentAttachmentsList as $attachment) {
//            $found = false;
//            foreach($newAttachmentsList as $item) {
//                if($attachment->id() === $item['id']) {
//                    $found = true;
//                    break;
//                }
//            }
//            
//            if(!$found) {
//                $this->userPostPhotos->remove($attachment);
//            }
//        }
//    }
    /**
     * @param array<string, string> $attachmentsData
     * @return array<int, PagePostAttachment>
     */
    function prepareAttachmentsForPagePost(User $requester, array $attachmentsData): array {
        $attachments = [];
        
        foreach ($attachmentsData as $attachmentData) {
            $id = $attachmentData['id'];
            $type = $attachmentData['type'];
            if($type === 'photo') {
                $attachments[] = $this->preparePhotoForUserPost($requester, $id);
            } elseif($type === 'video') {
                $attachments[] = $this->prepareVideoForUserPost($requester, $id);
            } elseif($type === 'animation') {
                $attachments = $this->prepareAnimationForUserPost($requester, $id);
            } else {
                throw new ValidationException("Incorrect type of attachment");
            }
        }
        return $attachments;
    }
    
    /**
     * @param array<string, string> $attachment
     */
    function prepareAttachmentForUserPost(User $requester, array $attachmentData): UserPostAttachment {
        $attachments = [];
        
        foreach ($attachmentsData as $attachmentData) {
            $id = $attachmentData['id'];
            $type = $attachmentData['type'];
            if($type === 'photo') {
                $attachments[] =  $this->preparePhotoForUserPost($requester, $id);
            } elseif($type === 'video') {
                $attachments[] =  $this->prepareVideoForUserPost($requester, $id);
            } elseif($type === 'animation') {
                $attachments[] =  $this->prepareAnimationForUserPost($requester, $id);
            } else {
                throw new ValidationException("Incorrect type");
            }
        }
        return $attachments;
    }
    
    function preparePhotoForUserPost(User $requester, string $id): PostPhoto {
        $photo = $this->userPostPhotos->getById($id);
        if(!$photo) {
            throw new UnprocessableRequestException(123, "Photo $id not found");
        }
        if($photo->isDeleted()) { // Фото из постов можно также убирать в корзину, если они не привязаны к посту(ам)
            throw new UnprocessableRequestException(111, "Photo $id in trash");
        }
        if(!$requester->equals($photo->creator())) {
            throw new UnprocessableRequestException(222, "Cannot add to post 'post photo' of another user");
        }
        return $photo;
    }
    
//    function preparePhotoForUserPost2(User $requester, string $id): PostPhoto {
//        $tempPhoto = $this->tempPostPhotos->getById($id);
//        if(!$tempPhoto) {
//            throw new UnprocessableRequestException(123, "'Temp' photo $id not found");
//        }
////        if($photo->isDeleted()) {
////            throw new UnprocessableRequestException(111, "Photo $id in trash");
////        }
//        if(!$requester->equals($tempPhoto->creator())) {
//            throw new UnprocessableRequestException(222, "Cannot add to post 'temp' photo of another user");
//        }
//        return $requester->createPostPhoto($this->postService->createPhotoVersionsFromPhoto($tempPhoto));
//    }
//    
    function preparePhotoForUserComment(string $id): CommentPhoto {
        $photo = $this->userCommentPhotos->getById($id);
        if(!$photo) {
            throw new UnprocessableRequestException(123, "Photo $id not found");
        }
        if($photo->isDeleted()) {
            throw new UnprocessableRequestException(123, "Photo $id in trash");
        }
        return $photo;
    }

//    function prepareVideoForNewUserPost(User $requester, string $id): UserVideo {
//        $video = $this->findUserVideo($id);
//        $this->failIfVideoDoesntBelongsToUser($requester, $video, "Cannot add/copy to post video ($id) of another user");
//        $this->failIfVideoFromComment($video, "Cannot add/copy to post video ($id) from comment");
//        $this->failIfUserVideoInTrash($video, "Cannot add/copy to post video ($id) of another user");
//        return $video->isTemp() ? $video : $requester->createVideo($video->versions());
//    }
//    
//    function prepareVideoForNewComment(User $requester, string $id): UserCommentVideo {
//        $video = $this->findUserVideo($id);
//        $this->failIfVideoDoesntBelongsToUser($requester, $video, "Cannot add/copy to comment video ($id) of another user");
//        $this->failIfVideoFromComment($video, "Cannot add/copy to comment video ($id) from comment");
//        $this->failIfUserVideoInTrash($video, "Cannot add/copy to comment video ($id) of another user");
//        return $video->isTemp() ? $video : $requester->createVideo($video->versions());
//    }
//    
//    function prepareUserVideoForExistingPost(User $requester, UserPost $post, string $id): UserVideo {        
//        $video = $this->findUserVideo($id);
//        if(!$video->post()->equals($post)) {
//            $this->failIfVideoDoesntBelongsToUser($requester, $video, "Cannot add/copy to post video ($id) of another user");
//            $this->failIfVideoFromComment($video, "Cannot add/copy to post video ($id) from comment");
//            $this->failIfUserVideoInTrash($video, "Cannot add/copy to post video ($id) of another user");
//            return $requester->createVideo($this->photoService->createVersionsFrom($video));
//        } else {
//            return $video;
//        }
//    }
//    
//    function prepareVideoForProfileComment(User $requester, ProfileComment $comment, string $id): UserVideo {       
//        $video = $this->findUserVideoOrFail($id, null);
//        if($video->isTemp() || $video->commentId() === $comment->id()) {
//            return $video;
//        } else {
//            $this->failIfVideoDoesntBelongToUser($requester, $video, "Cannot add/copy video of another user to comment ($id)");
//            $this->failIfVideoFromComment($video, "Cannot add/copy video from another comment to comment ($id)");
//            $this->failIfUserVideoInTrash($video, "Cannot add/copy video from trash to post ($id)");
//            return $requester->createVideo($video->src(), $video->previews());
//        }
//    }
//    
//    private function findUserVideo(string $id, string $message): UserVideo {
//        $video = $this->userVideos($id);
//        if(!$video) {
//            throw new UnprocessableRequestException(124, $message);
//        }
//        return $video;
//    }
    
//    function failIfUserPhotoInTrash(UserPhoto $photo, string $message) {
//        $inTrash = $photo->isDeleted()
//            || ($photo->post() && $photo->post()->isDeleted())
//            || ($photo->album() && $photo->album()->isDeleted());
//        
//        if($inTrash) {
//            throw new UnprocessableRequestException(123, $message);
//        }
//    }
//    
//    function failIfUserVideoInTrash(UserVideo $video, string $message) {
//        if($video->isDeleted() || ($video->post() && $video->post()->isDeleted())) {
//            throw new UnprocessableRequestException(123, $message);
//        }
//    }
}