<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Application\Exceptions\ForbiddenException;
use App\Application\Exceptions\NotExistException;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Authorization\UserAlbumPhotosAuth;
use App\Domain\Model\Users\Photos\Photo;
use App\Domain\Model\Users\ProfilePicture\ProfilePicture;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\PrivacyService\PrivacyService;
use App\Domain\Model\Users\Photos\Comments\Comment as PhotoComment;
use App\Domain\Model\Users\Photos\Reaction as PhotoReaction;
use App\Domain\Model\Users\Photos\Comments\Reaction as CommentReaction;
use App\Domain\Model\Users\Post\Comments\Comment as PostComment;
use App\Domain\Model\Users\Videos\Comments\Comment as VideoComment;
use App\Domain\Model\Users\Comments\ProfileComment;
use App\Domain\Model\Users\Comments\ProfileCommentRepository;
use App\Domain\Model\Users\PrivacyService\PrivacyResolver_new;

class UserPhotosAuth {
    use AuthorizationTrait;
    
    private ProfileCommentRepository $comments;
//    private CommentableAuth $commentableAuth;
    private UserPostsAuth $postsAuth;
    private UserAlbumPhotosAuth $albumsAuth;
    
    function __construct(ProfileCommentRepository $comments, UserPostsAuth $postsAuth, UserAlbumPhotosAuth $albumsAuth, PrivacyResolver_new $privacy) {
        $this->comments = $comments;
//        $this->commentableAuth = $commentableAuth;
        $this->postsAuth = $postsAuth;
        $this->albumsAuth = $albumsAuth;
        $this->privacy = $privacy;
    }
    /*
    // Если фото в корзине, или связанная сущность в корзине или фото временное, то такое фото будет доступно владельцу
    // Если код дошёл сюда, значит $requester не удалён и активен, поэтому, если он владелец фото, то он точно имеет доступ к это фото, поэтому если он владелец, то прекращаем выполнение
    
    // Если же $requester не является владельцем, то он не может видеть фото, если оно:
    // 1. В корзине
    // 2. Временное
    // 3. Если профиль закрыт
    // 4. Если $requester забанен владельцем фото
    // 5. Если связанная сущность не доступна
    // 6. Владелец может быть вообще удалён
    // 7. Владелец может быть не активен
    */
    
    function failIfCannotSeeCommentPhoto(User $requester, Photo $photo): void {
        $comment = $this->comments->getById($photo->commentId());
        if(!$comment) {
            throw new ForbiddenException(123, "No rights"); // как сам коммент был извлечён
        } /* Мне кажется, что это нужно проверить раньше при извлечении фото, коммента или реакции, но дело в том, что опять будет вероятность того, что коммент будет
        удалён между этими проверками. Мне кажется, что в таком случае нужно возвратить не 404, а 403, потому что это сервис авторизации, он не должен отвечать за 404

         Чтобы возвращать более адекватные сообщения, нужно отделить проверку приватности от проверки на то, находится ли комментарий в корзине */
        if(!$requester->equals($photo->owner()) && $comment->isSoftlyDeleted()) {
            throw new ForbiddenException(111, "No rights");
        }/*
             Владелец фото === владелец коммента, поэтому в случае, если владелец коммента был только что удалён(isDeleted), то это не будет браться во внимание, потому
             что проверка на то удалён ли владелец фото происходит раньше сразу после извлечения фото.
             Коммент может быть оставлен под постом, под фото и под видео, но у коммента и у любой из этой сущности один и тот же владелец, поэтому опять не нужно 
             проверять удалён ли владелец

             Проверка на бан ниже тоже не нужна
             В самом начале метода проверяется то, не является ли $requester владельцем. если он не владелец фото, то и не владелец ничего ниже

             Если комментарий оставлен под фото, то это фото точно не связано с комментом, потому что под фото к комментам нельзя оставлять комменты
         * 
         */
        if($comment instanceof AlbumPhotoComment) {
            $photo = $comment->commentedPhoto();
            $post = $photo->post();
            $album = $photo->album();
            $owner = $photo->owner();
            $ownerIsInactive = $this->isUserInactive($owner);
            if(!$requester->equals($owner) && ($comment->isSoftlyDeleted() || $photo->isSoftlyDeleted())) {
                throw new ForbiddenException(111, "No rights");
            }
            if(!$this->privacy->canSeeAlbum($requester, $album)) {
                throw new ForbiddenException(111, "Access is forbidden by privacy settings");
            }
        }
        elseif($comment instanceof VideoComment) {
            $video = $comment->commentedVideo();
            $owner = $video->owner();
            $ownerIsInactive = $this->isUserInactive($owner);
            if($comment->isDeleted() || $video->isDeleted() || $ownerIsInactive) {
                throw new ForbiddenException(111, "No rights");
            }
            $post = $video->post();
            if($video->isTemp() && !$requester->equals($photo->owner())) {
                throw new ForbiddenException(111, "No rights");
            } elseif($post) {
                if($post->isSoftlyDeleted()) {
                    throw new ForbiddenException(111, "No rights");
                }
                if(!$this->postsAuth->canSee($requester, $post)) {
                    throw new ForbiddenException(111, "Access is forbidden by privacy settings");
                }
            }
        }
        elseif($comment instanceof PostComment) {
            $post = $comment->commentedPost();
            if($post->isSoftlyDeleted() && !$requester->equals($post->creator())) {
                throw new ForbiddenException(111, "No rights");
            }
            if(!$post->isSoftlyDeleted() && !$this->postsAuth->canSee($requester, $post)) {
                throw new ForbiddenException(111, "Access is forbidden by privacy settings");
            }
        }
    }
    
    function failIfCannotSeePostPhoto(User $requester, PostPhoto $photo): void {
        $post = $photo->post();
        if($post->isSoftlyDeleted() && !$requester->equals($post->creator())) {
            throw new ForbiddenException(111, "No rights");
        }
        if(!$post->isSoftlyDeleted() && !$this->postsAuth->canSee($requester, $post)) {
            throw new ForbiddenException(111, "Access is forbidden by privacy settings");
        }
    }
    
    function canSee(User $requester, AlbumPhoto $photo): bool {
        try {
            $this->failIfCannotSeeAlbumPhoto($requester, $photo);
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
    
    function guestsCanSee(Album $photo): bool {
        try {
            $this->failIfGuestsCannotSee($photo);
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
    
    function failIfCannotSeeAlbumPhoto(User $requester, AlbumPhoto $photo): void {
        $album = $photo->album();
        if(!$requester->equals($photo->owner()) && $album->isSoftlyDeleted()) {
            throw new ForbiddenException(111, "No rights");
        }
        elseif(!$this->privacy->canSeeAlbum($requester, $album)) {
            throw new ForbiddenException(111, "Access is forbidden by privacy settings");
        }
    }
    
    function failIfCannotSee(User $requester, Photo $photo): void {
        if($photo->isDeletedByManager()) { // Если фото удалено модератором соц сети, то фото могут увидеть только модераторы
            if($requester->isModer() && !$requester->equals($photo->owner())) { // Если владелец является модератором и НЕ является владельцем фото, то просмотр доступен
                return; // Это значит, что если владелец фото является модератором, то для него доступ к фото запрещен
            } else {
                throw new ForbiddenException(123, "No rights"); // Даже владелец не может посмотреть
            }
        }
        if($requester->equals($photo->owner())) {
            return;
        }
        // Если $requster не является владельцем, то есть вероятность, что владелец фото не активен или удалён, если же $requester является владельцем, то значит он не
        $this->failIfUserIsInactive($photo->owner(), "Access forbidden, profile owner is "); // удалён и активен, если бы было иначе, то всё бы закончилось ещё в методе findRequesterOrFailIfNotFoundOrInactive

        if($photo->isDeleted() && !$requester->equals($photo->owner())) { // Если $requester не является владельцем, то ему запрещен доступ к фото в корзине. Но этой проверки недостаточно, ведь isDeleted может быть false, но 
            throw new ForbiddenException(111, "No rights"); // при это фото всё равно может находиться в корзине, если связанный пост или коммент в корзине
        } // Может быть такое, что у связанного коммента isDeleted false, но сам коммент может быть связан с сущность, у которой isDeleted true.
        $this->failIfInBlacklist($requester, $photo->owner(), "Acceess is forbidden, banned by profile owner");
        
        if(!$this->privacy->isProfileAccessibleTo($requester, $photo->owner())) { // Если $requester не владелец, то нужно проверить доступность профиля владельца, если он недоступен, то и
            $this->throwPrivacyException(); // фото ТОЧНО недоступно и дальше нет смысла проверять
        } /*
         * Дальше уже нужно проверять доступ к сущностям, к которым фото добавлено. Удобнее всего делать это с помощью спец. сервисов для авторизации, но минус такого подхода в том, что
         * там повторяются такие проверки:
         * 1. Активен ли владелец
         * 2. Не удалён ли владелец
         * 3. Доступен ли профиль владельца
         * 4. Не забанен ли $requester
         * 
         * Но при этом там есть проверки, которых здесь нет. Здесь сложно реализовать проверку того, не находится ли комментарий, с которым связано фото, в корзине.
         * Но здесь легко проверить находится ли пост или альбом в корзине.
         * 
         * Но вот проверки настроек приватности здесь реализовать сложнее, 
         */
        $commentId = $photo->commentId();
        $post = $photo->post();
        $album = $photo->album();
        if($commentId) {
            $comment = $this->comments->getById($commentId); // Есть несколько типов комментариев в профиле: UserPostComment, UserPhotoComment и UserVideoComment
            if(!$comment) { // Комментарий может быть не найден, но вероятность крайне мала, чтобы это было возможно, нужно чтобы коммент был удалён сразу после того, как фото было извлечено и перед тем, как
                throw new ForbiddenException(123, "No rights"); // как сам коммент был извлечён
            } /* Мне кажется, что это нужно проверить раньше при извлечении фото, коммента или реакции, но дело в том, что опять будет вероятность того, что коммент будет
            удалён между этими проверками. Мне кажется, что в таком случае нужно возвратить не 404, а 403, потому что это сервис авторизации, он не должен отвечать за 404
            
             Чтобы возвращать более адекватные сообщения, нужно отделить проверку приватности от проверки на то, находится ли комментарий в корзине */
            if(!$requester->equals($photo->owner()) && $comment->isSoftlyDeleted()) {
                throw new ForbiddenException(111, "No rights");
            }/*
                 Владелец фото === владелец коммента, поэтому в случае, если владелец коммента был только что удалён(isDeleted), то это не будет браться во внимание, потому
                 что проверка на то удалён ли владелец фото происходит раньше сразу после извлечения фото.
                 Коммент может быть оставлен под постом, под фото и под видео, но у коммента и у любой из этой сущности один и тот же владелец, поэтому опять не нужно 
                 проверять удалён ли владелец
                
                 Проверка на бан ниже тоже не нужна
                 В самом начале метода проверяется то, не является ли $requester владельцем. если он не владелец фото, то и не владелец ничего ниже
                
                 Если комментарий оставлен под фото, то это фото точно не связано с комментом, потому что под фото к комментам нельзя оставлять комменты
             * 
             */
            if($comment instanceof PhotoComment) {
                $photo = $comment->commentedPhoto();
                $post = $photo->post();
                $album = $photo->album();
                $owner = $photo->owner();
                $ownerIsInactive = $this->isUserInactive($owner);
                if(!$requester->equals($owner) && ($comment->isSoftlyDeleted() || $photo->isDeleted() || $ownerIsInactive)) {
                    throw new ForbiddenException(111, "No rights");
                }
                
                if($photo->isTemp() && !$requester->equals($photo->owner())) {
                    throw new ForbiddenException(111, "No rights");
                } elseif($post && !$this->postsAuth->canSee($requester, $post)) {
                    if($post->isSoftlyDeleted()) {
                        throw new ForbiddenException(111, "No rights");
                    }
                    throw new ForbiddenException(111, "Access is forbidden by privacy settings");
                } elseif($album && !$this->privacy->canSeeAlbum($requester, $album)) {
                    throw new ForbiddenException(111, "Access is forbidden by privacy settings");
                }/* Если фото является картинкой профиля, то здесь не обязательно проверять есть ли доступ к профилю, потому что это уже сделано выше
                elseif($photo->picture() && $this->privacy->userHasAccessToProfile($requester, $photo->owner())) {
                    throw new ForbiddenException(111, "Access is forbidden by privacy settings");
                } */
            }
            elseif($comment instanceof VideoComment) {
                $video = $comment->commentedVideo();
                $owner = $video->owner();
                $ownerIsInactive = $this->isUserInactive($owner);
                if($comment->isDeleted() || $video->isDeleted() || $ownerIsInactive) {
                    throw new ForbiddenException(111, "No rights");
                }
                $post = $video->post();
                if($video->isTemp() && !$requester->equals($photo->owner())) {
                    throw new ForbiddenException(111, "No rights");
                } elseif($post) {
                    if($post->isSoftlyDeleted()) {
                        throw new ForbiddenException(111, "No rights");
                    }
                    if(!$this->postsAuth->canSee($requester, $post)) {
                        throw new ForbiddenException(111, "Access is forbidden by privacy settings");
                    }
                }
            }
            elseif($comment instanceof PostComment) {
                $post = $comment->commentedPost();
                if($post->isSoftlyDeleted()) {
                    throw new ForbiddenException(111, "No rights");
                }
                if(!$this->postsAuth->canSee($requester, $post)) {
                    throw new ForbiddenException(111, "Access is forbidden by privacy settings");
                }
            }
        }
        elseif($post) {
            if(!$requester->equals($photo->owner()) && $post->isSoftlyDeleted()) {
                throw new ForbiddenException(111, "No rights");
            }
            elseif(!$this->postsAuth->canSee($requester, $post)) { // У поста специфические настройки приватности, я даже не уверен как в конечном итоге они будут выглядеть. Если их можно будет всунуть проверку приватности поста в PrivacyService,
                throw new ForbiddenException(111, "Access is forbidden by privacy settings");// можно будет не использовать UserPostsAuth::failIfCannotSee()
            }
        }
        elseif($album) { // В вк альбомы удаляются без возможности восстановления, мне кажется, что это неправильно
            if(!$requester->equals($photo->owner()) && $album->isSoftlyDeleted()) {
                throw new ForbiddenException(111, "No rights");
            }
            elseif(!$this->privacy->canSeeAlbum($requester, $album)) {
                throw new ForbiddenException(111, "Access is forbidden by privacy settings");
            }
        }
        elseif($photo->isTemp() && !$requester->equals($photo->owner())) { // Если фото не привязано ни к чему (временное), то только создатель может видеть его
            throw new ForbiddenException(111, "No rights");
        }
    }
    
    function failIfCannotUpdateAsManager(User $requester, Photo $photo): void {
        // Я пока не знаю какая логика здесь будет. Пока что будем считать, что модераторы могут удалить чьё угодно фото
        if(!$requester->isModer()) {
            throw new ForbiddenException(111, "No rights");
        }
    }
    
    function failIfGuestsCannotSee(Photo $photo): void {
        $this->failIfUserIsInactive($photo->owner(), "Access forbidden, profile owner is ");

        if($photo->isDeleted()) {
            throw new ForbiddenException(111, "No rights");
        }
        $this->failIfGuestsCannotAccessProfile($photo->owner());
        
        $commentId = $photo->commentId();
        $post = $photo->post();
        $album = $photo->album();
        if($commentId) {
            $comment = $this->comments->getById($commentId);
            if(!$comment) {
                throw new ForbiddenException(111, "No rights");
            }
            if($comment instanceof PhotoComment) {
                $photo = $comment->commentedPhoto();
                $owner = $photo->owner();
                $ownerIsInactive = $this->isUserInactive($owner);
                if($comment->isSoftlyDeleted() || $photo->isDeleted() || $ownerIsInactive) {
                    throw new ForbiddenException(111, "No rights");
                }
                $post = $photo->post();
                $album = $photo->album();
                if($photo->isTemp()) {
                    throw new ForbiddenException(111, "No rights");
                } elseif($post && !$this->postsAuth->guestsCanSee($post)) {
                    throw new ForbiddenException(111, "Access is forbidden by privacy settings");
                } elseif($album && !$this->privacy->canGuestSeeAlbum($album)) {
                    throw new ForbiddenException(111, "Access is forbidden by privacy settings");
                }
            }
            elseif($comment instanceof VideoComment) {
                $video = $comment->commentedVideo();
                $owner = $video->creator();
                $ownerIsInactive = $this->isUserInactive($owner);
                if($comment->isDeleted() || $video->isDeleted() || $ownerIsInactive) {
                    throw new ForbiddenException(111, "No rights");
                }
                $post = $video->post();
                if($video->isTemp()) {
                    throw new ForbiddenException(111, "No rights");
                } elseif($post && !$this->postsAuth->guestsCanSee($post)) {
                    throw new ForbiddenException(111, "Access is forbidden by privacy settings");
                }
                // Если видео обычное, то проверка не нужна, потому что достаточно проверки доступности профиля, которая уже есть выше
            }
            elseif($comment instanceof PostComment) {
                $post = $comment->commentedPost();
                $owner = $post->creator();
                $ownerIsInactive = $this->isUserInactive($owner);
                if($comment->isDeleted() || $post->isSoftlyDeleted() || $ownerIsInactive) {
                    throw new ForbiddenException(111, "No rights");
                }
                if(!$this->postsAuth->guestsCanSee($post)) {
                    throw new ForbiddenException(111, "Access is forbidden by privacy settings");
                }
            }
        } elseif($post) {
            if(!$post->isSoftlyDeleted()) {
                throw new ForbiddenException(111, "No rights");
            }
            if(!$this->postsAuth->guestsCanSee($post)) {
                throw new ForbiddenException(111, "Access is forbidden by privacy settings");
            }
        } elseif($album) {
            if(!$this->privacy->canGuestSeeAlbum($album)) {
                throw new ForbiddenException(111, "Access is forbidden by privacy settings");
            }
        } else { // Если фото не привязано ни к чему, то гость точно не может его видеть
            throw new ForbiddenException(111, 'No rights');
        }
    }
    
    function failIfPhotoInactive(Photo $photo): void {
        $this->failIfUserIsInactive($photo->owner(), "Account of photo owner is "); // Проверяем заморожен ли пользователь, или может быть он приостановил аккаунт
        $this->failIfUserIsDeleted($photo->owner(), "Account of photo owner is deleted");
        if($photo->isDeleted()) { // Мне кажется, что если пост помечен удалённым, то не стоит возвращать 404, потому что это не секрет, что посты не удаляются из БД сразу, а только через некоторое
            throw new ForbiddenException(111, "Access to photo is forbidden, photo in recycle bin"); // время. Если возвратить 403, то это не станет шоком для запрашивающего
        }
    }
    
//    function failIfCannotShare(User $requester, Photo $photo): void {
//        if($photo->isTemp()) {
//            throw new ForbiddenException(111, "Cannot share temp photo");
//        } elseif($photo->commentId()) {
//            throw new ForbiddenException(111, "Cannot share photo from comment");
//        } elseif($photo->post()) {
//            throw new ForbiddenException(111, "Cannot share photo from post");
//        } elseif($photo->album()) { // Можно поделиться только фотографией с альбома или аватаркой
//            if(!$this->privacy->canSeeAlbum($requester, $photo->album())) {
//                throw new UnprocessableRequestException(123, "Cannot share photo {$photo->id()}, access prohibited");
//            }
//        } elseif($photo->isAvatar()) {
//            if(!$this->privacy->isProfileAccessibleTo($requester, $photo->owner())) {
//                throw new UnprocessableRequestException(123, "Cannot share photo {$photo->id()}, access prohibited");
//            }
//        }
//    }
    
    function failIfCannotUpdateReaction(User $requester, PhotoReaction $reaction): void {
        $this->failIfCannotSee($requester, $reaction->photo()); // Чтобы изменить реакцию к фото, нужно чтобы было доступно фото
        if(!$requester->equals($reaction->creator())) { // и быть создателем реакции
            throw new ForbiddenException(123, "Cannot update reaction that was created by another user");
        }
    }
    
    function failIfCannotSeeComment(User $requester, PhotoComment $comment): void {
        if($comment->isSoftlyDeleted()) {
            throw new ForbiddenException(123, "Access is forbidden, comment in recycle bin");
        }
        $this->failIfCannotSee($requester, $comment->commentedPhoto()); // ведь если фото доступно, то доступен и коммент
    }
    
    function failIfCannotUpdateComment(User $requester, PhotoComment $comment): void {
        // Удалить любой коммент может владелец профиля
        if($requester->equals($comment->creator())) {
            return;
        }
        $this->failIfCannotSeeComment($requester, $comment); // Если фото не доступно для создателя коммента, то он не сможет удалить коммент
        if(!$requester->equals($comment->creator())) { // Также удалить может создатель коммента.
            throw new ForbiddenException(123, "Cannot delete comment that was created by another user");
        }
    }
    
    function failIfCannotDeleteComment(User $requester, PhotoComment $comment, bool $byManager): void {
        // Удалить любой коммент может владелец профиля
        if($byManager) {
            
        }
        if($requester->equals($comment->owner())) {
            return;
        }
        $this->failIfCannotSeeComment($requester, $comment); // Если фото не доступно для создателя коммента, то он не сможет удалить коммент
        if(!$requester->equals($comment->creator())) { // Также удалить может создатель коммента.
            throw new ForbiddenException(123, "Cannot delete comment that was created by another user");
        }
    }
    
    function failIfCannotUpdateCommentReaction(User $requester, CommentReaction $reaction): void {
        $this->failIfCannotSeeComment($requester, $reaction->comment()); // Чтобы изменить реакцию к комменту, нужно чтобы был доступен коммент
        if(!$requester->equals($reaction->creator())) { // и быть создателем реакции
            throw new ForbiddenException(123, "Cannot update reaction that was created by another user");
        }
    }
    
    function failIfCannotDeleteCommentReaction(User $requester, CommentReaction $reaction): void {
        $this->failIfCannotSeeComment($requester, $reaction->comment());
        if(!$requester->equals($reaction->creator())) { // и быть создателем реакции
            throw new ForbiddenException(123, "Cannot delete reaction that was created by another user");
        }
    }


    
    function failIfCannotUpdate(User $requester, Photo $photo): void {
        if(!$requester->equals($photo->owner())) {
            throw new ForbiddenException(123, "No rights");
        }
//        if($photo->commentId() && !$requester->equals($photo->creator())) { // Если фото добавлено к комменту, то его может изменить только создатель фото
//            throw new ForbiddenException(111, 'Cannot update photo');
//        } elseif(!$photo->commentId() && !$requester->equals($photo->owner())) { // Если фото не к комменту, то его может изменить только владелец профиля, где находится это фото
//            throw new ForbiddenException(111, 'Cannot update photo');
//        }
    }
    
    function failIfCannotUpdatePicture(User $requester, ProfilePicture $photo): void {
        if(!$requester->equals($photo->owner())) {
            throw new ForbiddenException(111, 'Cannot update profile picture of another user');
        }
    }
    
    function failIfCannotDelete(User $requester, Photo $photo): void {
        if(!$requester->equals($photo->owner())) {
            throw new ForbiddenException(123, "Cannot delete photo of another user");
        }
    }
}