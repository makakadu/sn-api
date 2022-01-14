<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

use App\Application\Exceptions\UnprocessableRequestException;
use App\Domain\Model\Users\Post\Post as UserPost;
use App\Application\CheckPhotoIsActiveVisitor;

use App\Domain\Model\Users\Photos\Photo as UserPhoto;
use App\Domain\Model\Users\Videos\Video as UserVideo;

use App\Domain\Model\Users\Videos\VideoRepository;
//use App\Domain\Model\Users\Photos\PhotoRepository;
use App\Domain\Model\Users\Post\PostRepository;

use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Authorization\UserPhotosAuth;
use App\Domain\Model\Authorization\UserVideosAuth;
use App\Domain\Model\Users\User\User;

use App\Domain\Model\Users\Comments\Photo\Photo as CommentPhoto;
use App\Domain\Model\Users\Comments\Video\Video as CommentVideo;

use App\Domain\Model\Users\Photos\Comments\Comment as UserPhotoComment;
use App\Domain\Model\ProfileComment;

use App\Domain\Model\TempPhoto\TempPhoto;

use App\Domain\Model\Users\Post\Photo\Photo as PostPhoto;
use App\Domain\Model\Users\Post\Photo\PhotoRepository as PostPhotoRepository;

use App\Domain\Model\Users\Comments\Photo\PhotoRepository as CommentPhotoRepository;

class AttachmentsService {
    
    //private CommentRepository $comments;
    private PhotoService $photoService;
    
    //private PhotoRepository $userPhotos;
    private VideoRepository $videos;
    //private PostRepository $posts;
    
    private UserPhotosAuth $photosAuth;
    private UserVideosAuth $videosAuth;
    private UserPostsAuth $postsAuth;
    
    private PostPhotoRepository $userPostPhotos;
    private CommentPhotoRepository $userCommentPhotos;

//    private GroupPhotosAuth $groupPhotosAuth;
//    private GroupVideosAuth $groupVideosAuth;
//    private GroupPostsAuth $groupPostsAuth;
    function __construct(
            PostPhotoRepository $userPostPhotos,
            CommentPhotoRepository $userCommentPhotos,
            PhotoService $photoService,
            //PhotoRepository $photos,
            VideoRepository $videos
            //PostRepository $posts
//            UserPhotosAuth $photosAuth,
//            UserVideosAuth $videosAuth,
//            UserPostsAuth $postsAuth
    ) {
        $this->userPostPhotos = $userPostPhotos;
        $this->userCommentPhotos = $userCommentPhotos;
        $this->photoService = $photoService;
        //$this->userPhotos = $photos;
        $this->videos = $videos;
        //$this->posts = $posts;
//        $this->photosAuth = $photosAuth;
//        $this->videosAuth = $videosAuth;
//        $this->postsAuth = $postsAuth;
    }
    
    function removeOldAttachmentFromProfileCommentIfNeed(ProfileComment $comment, string $newAttachmentId) {
        if($comment->photo() && $comment->photo()->id() !== $newAttachmentId) {
            $this->userPhotos->remove($comment->photo());
        } elseif($comment->post() && $comment->post()->id() !== $newAttachmentId) {
            $this->videos->remove($comment->post());
        }
    }
    
    function removeOldAttachmentsFromPost(UserPost $post, array $newAttachmentsList) {
        $currentAttachmentsList = $post->attachments();
        
        foreach ($currentAttachmentsList as $attachment) {
            $found = false;
            foreach($newAttachmentsList as $item) {
                if($attachment->id() === $item['id']) {
                    $found = true;
                    break;
                }
            }
            
            if(!$found) {
                $this->userPostPhotos->remove($attachment);
            }
        }
    }
    
    function preparePhotoForNewUserPost_with_temp(User $requester, string $id): UserPhoto {
        /*
        // Фото должно принадлежать запрашивающему
        // Должно быть активным, то есть не быть в корзине. Также не должны быть в корзине сущности, с которыми связано фото
        // Не должно быть связано с комментом
        // То есть выполняется очень мало проверок по сравнению с PhotosAuth::failIfCannotSee(), тем более там не будет выброшено исключение, если фото в корзине, короче говоря, этот метод
        // бесполезен
        
        // Именно здесь нужно сделать все эти проверки, потому что именно здесь может быть создана копия, а чтобы создать копию, нужно убедиться, что оригинал подходит под критерии
        // Но мне кажется, что и в конструкторе поста и в методе Post::edit() нужно сделать проверку на то принадлежит ли фото создателю поста
        */
        $photo = $this->findTempPhotoOrFail($id, "Photo $id not found");
        $this->failIfPhotoDoesntBelongToUser($requester, $photo, "Cannot add/copy to post photo ($id) of another user");
        $this->failIfPhotoFromComment($photo, "Cannot add/copy to post photo ($id) from comment");
        $this->failIfUserPhotoInTrash($photo, "Cannot add/copy to post photo ($id) from trash");

        return $photo->isTemp() ? $photo : $requester->create($photo->versions());
    }
    
    function preparePhotoForUserPost(User $requester, string $id): PostPhoto {
        $photo = $this->userPostPhotos->getById($id);
        if(!$photo) {
            throw new UnprocessableRequestException(123, "Photo $id not found");
        }
        if($photo->isDeleted()) { // Фото из постов можно также убирать в корзину, если они не привязаны к посту(ам)
            throw new UnprocessableRequestException("Photo $id in trash");
        }
        if(!$requester->equals($photo->creator())) {
            throw new UnprocessableRequestException("Cannot add to post 'post photo' of another user");
        }
        return $photo;
    }
    
    function preparePhotoForUserComment(string $id): CommentPhoto {
        $photo = $this->userCommentPhotos->getById($id);
        if(!$photo) {
            throw new UnprocessableRequestException(123, "Photo $id not found");
        }
        if($photo->isDeleted()) {
            throw new UnprocessableRequestException("Photo $id in trash");
        }
        return $photo;
    }
/* 
//    function prepareUserPhotoForNewComment(User $requester, string $id): UserPhoto {
//        $photo = $this->findTempPhotoOrFail($id, "Photo $id not found");
//        $this->failIfPhotoDoesntBelongToUser($requester, $photo, "Cannot add/copy to comment photo ($id) of another user");
//        $this->failIfPhotoFromComment($photo, "Cannot add/copy to comment photo ($id) from comment");
//        $this->failIfUserPhotoInTrash($photo, "Cannot add/copy to commentphoto ($id) from trash");
//
//        return $photo->isTemp() ? $photo : $requester->create($photo->versions());
//    }
//
//    function preparePhotoForExistingUserPost(User $requester, UserPost $post, string $type, string $id): UserPostPhoto {        
//        $photo = $this->findPostPhotoOrFail($id, "Photo $id not found");
//        if($photo->isDeleted()) { // Фото из постов можно также убирать в корзину, если они не привязаны к посту(ам)
//            throw new UnprocessableRequestException("Photo $id in trash");
//        }
//    }
//    
//    function preparePhotoForProfileComment(User $requester, ProfileComment $comment, string $id): UserPhoto {
//        $photo = $this->findTempPhotoOrFail($id, null); // Фото должно либо быть уже в комменте либо временное
//        if($photo->isTemp() || $photo->commentId() === $comment->id()) { // Здесь есть проблемка, дело в том, что для комментов из профиля существует 3 таблицы и у комментов
//            return $photo; // с разных таблиц может совпасть ID. Поэтому нужно сделать либо так, что ID точно будут отличаться, либо как-то удостовериться, что сравниваемые
//        } else { // комменты находятся в разных таблицах
//            // В противном случае создаётся новое фото на основе переданного, я пока ещё точно не решил стоит ли делать так
//            $this->failIfPhotoDoesntBelongToUser($requester, $photo, "Cannot add/copy photo to comment ($id) of another user");
//            $this->failIfPhotoFromComment($photo, "Cannot add/copy to comment photo ($id) from another comment");
//            $this->failIfUserPhotoInTrash($photo, "Cannot add/copy to post photo ($id) from trash");
//            return $requester->createPhoto($this->photoService->createPhotoVersionsBasedOnPhoto($photo));
//        }
//    }
 * 
 */
    
    function preparePhotoForCommentToUserAlbumPhoto(User $requester, UserAlbumPhoto $photo, string $commentPhotoId) {
        $commentPhoto = $this->commentPhotos->getById($commentPhotoId);
        if(!$commentPhoto->creator()->equals($requester)) {
            throw new UnprocessableRequestException(123, "Cannot add 'comment photo' of another user to comment");
        }
        $photoCreator = $photo->creator();
        // Здесь нужно узнать не находится ли владелец комментируемого фото на другом шарде, если да, то создаём новое фото.
        // Но есть одна проблема, дело в том, что у CommentPhoto есть свойство $creator, которое содержит ссылку на User, а это значит, что User и CommentPhoto должны находиться 
        // на одном шарде, иначе при извлечении CommentPhoto в его свойство $creator не будет внедрён User, возможно даже будет ошибка. Возможно стоит убрать это свойство, ведь оно 
        // не очень то и нужно, но нужно чтобы CommentPhoto было как-то привязано к пользователю, чтобы можно было определить может ли пользователь использовать это фото, есть ли у него
        // права на то, чтобы добавить это фото в коммент.
        // Эта проблемка будет только, если будет использоваться шардинг, но я пока что не использую его, поэтому не буду усложнять себе жизнь
    }
    
    function prepareVideoForNewUserPost(User $requester, string $id): UserVideo {
        $video = $this->findUserVideo($id);
        $this->failIfVideoDoesntBelongsToUser($requester, $video, "Cannot add/copy to post video ($id) of another user");
        $this->failIfVideoFromComment($video, "Cannot add/copy to post video ($id) from comment");
        $this->failIfUserVideoInTrash($video, "Cannot add/copy to post video ($id) of another user");
        return $video->isTemp() ? $video : $requester->createVideo($video->versions());
    }
    
    function prepareVideoForNewComment(User $requester, string $id): CommentVideo {
        $video = $this->findUserVideo($id);
        $this->failIfVideoDoesntBelongsToUser($requester, $video, "Cannot add/copy to comment video ($id) of another user");
        $this->failIfVideoFromComment($video, "Cannot add/copy to comment video ($id) from comment");
        $this->failIfUserVideoInTrash($video, "Cannot add/copy to comment video ($id) of another user");
        return $video->isTemp() ? $video : $requester->createVideo($video->versions());
    }
    
    function prepareUserVideoForExistingPost(User $requester, UserPost $post, string $id): UserVideo {        
        $video = $this->findUserVideo($id);
        if(!$video->post()->equals($post)) {
            $this->failIfVideoDoesntBelongsToUser($requester, $video, "Cannot add/copy to post video ($id) of another user");
            $this->failIfVideoFromComment($video, "Cannot add/copy to post video ($id) from comment");
            $this->failIfUserVideoInTrash($video, "Cannot add/copy to post video ($id) of another user");
            return $requester->createVideo($this->photoService->createVersionsFrom($video));
        } else {
            return $video;
        }
    }
    
    function prepareVideoForProfileComment(User $requester, ProfileComment $comment, string $id): UserVideo {       
        $video = $this->findUserVideoOrFail($id, null);
        if($video->isTemp() || $video->commentId() === $comment->id()) {
            return $video;
        } else {
            $this->failIfVideoDoesntBelongToUser($requester, $video, "Cannot add/copy video of another user to comment ($id)");
            $this->failIfVideoFromComment($video, "Cannot add/copy video from another comment to comment ($id)");
            $this->failIfUserVideoInTrash($video, "Cannot add/copy video from trash to post ($id)");
            return $requester->createVideo($video->link(), $video->previews());
        }
    }
    
    function prepareGroupPhotoForNewPost(User $requester, Group $group, string $type, string $id) {
        if($type === 'group-photo') {
            $photo = $this->findGroupPhoto($id);
            $this->failIfPhotoDoesntBelongToGroup($group, $photo);
            $this->groupPhotosAuth->failIfCannotSee($requester, $photo); // Если пользователь имеет доступ к фото с группы, то он может добавить его к посту
            $this->failIfPhotoFromComment($photo);
            return $group->create($requester, $photo);
        }
        elseif($type === 'user-photo') {
            $photo = $this->findTempPhotoOrFail($id);
            $this->failIfPhotoDoesntBelongToUser($requester, $photo);
            $this->failIfPhotoFromComment($photo);
            return $group->create($requester, $photo);
        }
    }
    
    function prepareGroupPhotoForExistingPost(User $requester, GroupPost $post, Group $group, string $type, string $id) {
        if($type === 'group-photo') {
            $photo = $this->findGroupPhoto($id);
            if(!$post->equals($photo->commentedPost())) {
                $this->failIfPhotoDoesntBelongToGroup($group, $photo);
                $this->groupPhotosAuth->failIfCannotSee($requester, $photo);
                $this->failIfPhotoFromComment($photo);
                return $group->create($requester, $this->photoService->createVersionsFromPhoto($photo));
            } else {
                return $photo; // Если фото уже связано с постом, то просто добавляем его
            }
        } elseif($type === 'user-photo') {
            $photo = $this->findTempPhotoOrFail($id);
            $this->failIfPhotoDoesntBelongToUser($requester, $photo);
            $this->failIfPhotoFromComment($photo);
            return $group->create($requester, $this->photoService->createVersionsFromPhoto($photo));
        }
    }
    
    function prepareGroupVideoForNewPost(User $requester, Group $group, string $type, string $id) {
        if($type === 'group-video') {
            $video = $this->findGroupVideo($id);
            $this->failIfVideoDoesntBelongToGroup($group, $video);
            $this->groupVideosAuth->failIfCannotSee($requester, $video); // Если пользователь имеет доступ к видео с группы, то он может добавить его к посту
            $this->failIfVideoFromComment($video);
            return $group->createVideo($requester, $video);
        }
        elseif($type === 'user-video') {
            $video = $this->findUserVideo($id);
            $this->failIfVideoDoesntBelongToUser($requester, $video);
            $this->failIfVideoFromComment($video);
            return $group->createVideo($requester, $video);
        }
    }
    
    function prepareGroupVideoForExistingPost(User $requester, GroupPost $post, Group $group, string $type, string $id) {
        if($type === 'group-video') {
            $video = $this->findGroupVideo($id);
            if(!$post->equals($video->commentedPost())) {
                $this->failIfVideoDoesntBelongToGroup($group, $video);
                $this->groupVideosAuth->failIfCannotSee($requester, $video);
                $this->failIfVideoFromComment($video);
                return $group->createVideo($requester, $this->photoService->createVersionsFromVideo($video));
            } else {
                return $video; // Если видео уже связано с постом, то просто добавляем его
            }
        } elseif($type === 'user-video') {
            $video = $this->findUserVideo($id);
            $this->failIfVideoDoesntBelongToUser($requester, $video);
            $this->failIfVideoFromComment($video);
            return $group->createVideo($requester, $this->photoService->createVersionsFromPhoto($video));
        }
    }
    
    private function failIfPhotoFromComment(UserPhoto $photo) {
        if($photo->commentId()) {
            throw new UnprocessableRequestException(124, "Cannot create photo for post/comment based on photo from another comment");
        }
    }
    
    private function failIfPhotoDoesntBelongToUser(User $requester, UserPhoto $photo) {
        if(!$requester->equals($photo->owner())) {
            throw new UnprocessableRequestException(124, "Cannot add/copy to post photo ({$photo->id()}) of another user");
        }
    }
    
    // Этот метод отвечает только за поиск фото и выброс исключения в случае ненахода, всё остальное - это только рассуждения
    private function findTempPhotoOrFail(string $id, ?string $message): TempPhoto {
        // Поскольку классов фото слишком много, то я решил, что фото для постов и комментов будут создаваться только на основе TempPhoto
        
        $photo = $this->userPhotos->getById($id);
        // Фото может быть не найдено
        // Фото может быть помечено удалённым
        // Фото может быть из поста или коммента, котороые помечены удалённым
        // Возможно, что фото с коммента, а коммент принадлежит сущности, которая помечена удалённой
        // Фото может принадлежать пользователю, которого временно забанили, или он временно приостановил свой аккаунт
        // Фото может принадлежать пользователю, который как бы и окончательно удалён, но всё ещё находится в БД, на всякий случай так сказать
        
        // Поскольку запрашивающий может прикрепить только фото, которое принадлежит ему, то есть если это фото принадлежит запрашивающему, то владелец точно не удалён, поэтому смысла проверять это
        // нет, но лучше сделать это, потому что это может быть фото другого пользователя и было бы неплохо возвратить сообщение "Photo not found".
        if(!$photo || $photo->owner()->isSoftlyDeleted()) {
            throw new UnprocessableRequestException(124, $message ?? "Photo $id not found");
        }
        return $photo;
    }
    
    private function findUserVideo(string $id, string $message): UserVideo {
        $video = $this->userVideos($id);
        if(!$video) {
            throw new UnprocessableRequestException(124, $message);
        }
        return $video;
    }
    
    private function findGroupPhoto(string $id): GroupPhoto {
        $photo = $this->groupPhotos($id);
        if(!$photo || ($photo && $photo->accept(new CheckPhotoIsActiveVisitor($this->comments)))) {
            throw new UnprocessableRequestException(124, "Photo $id not found");
        }
        return $photo;
    }
    
    private function findGroupVideo(string $id): GroupVideo {
        $video = $this->groupVideos($id);
        if(!$video || ($video && $video->accept(new CheckVideoIsActiveVisitor($this->comments)))) {
            throw new UnprocessableRequestException(124, "Video $id not found");
        }
        return $video;
    }
    
    function failIfPhotoDoesntBelongToGroup(Group $group, GroupPhoto $photo) {
        if(!$group->equals($photo->group())) {
            throw new UnprocessableRequestException(124, "Cannot create photo for post based on photo ({$photo->id()}) from another group");
        }
    }
    
    function failIfUserPhotoInTrash(UserPhoto $photo, string $message) {
        $inTrash = $photo->isDeleted()
            || ($photo->post() && $photo->post()->isSoftlyDeleted())
            || ($photo->album() && $photo->album()->isSoftlyDeleted());
        
        if($inTrash) {
            throw new UnprocessableRequestException(123, $message);
        }
    }
    
    function failIfUserVideoInTrash(UserVideo $video, string $message) {
        if($video->isDeleted() || ($video->post() && $video->post()->isSoftlyDeleted())) {
            throw new UnprocessableRequestException(123, $message);
        }
    }
}