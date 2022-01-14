<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Application\Exceptions\UnprocessableRequestException;
use App\Domain\Model\Users\User\User;

use App\Domain\Model\Users\Photos\AlbumPhoto\AlbumPhotoRepository as UserAlbumPhotoRepository;
use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePictureRepository;
use App\Domain\Model\Users\Videos\VideoRepository as UserVideoRepository;
use App\Domain\Model\Users\Post\PostRepository as UserPostRepository;

use App\Domain\Model\Groups\Photos\AlbumPhoto\AlbumPhotoRepository as GroupAlbumPhotoRepository;
use App\Domain\Model\Groups\Photos\GroupPicture\GroupPictureRepository as GroupPictureRepository;
use App\Domain\Model\Groups\Videos\VideoRepository as GroupVideoRepository;
use App\Domain\Model\Groups\Post\PostRepository as GroupPostRepository;

use App\Domain\Model\Pages\Photos\AlbumPhoto\AlbumPhotoRepository as PageAlbumPhotoRepository;
use App\Domain\Model\Pages\Photos\PagePicture\PagePictureRepository as PagePictureRepository;
use App\Domain\Model\Pages\Videos\VideoRepository as PageVideoRepository;
use App\Domain\Model\Pages\Post\PostRepository as PagePostRepository;

use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Authorization\UserAlbumPhotosAuth;
use App\Domain\Model\Authorization\ProfilePicturesAuth;
use App\Domain\Model\Authorization\UserVideosAuth;

use App\Domain\Model\Authorization\GroupPostsAuth;
use App\Domain\Model\Authorization\GroupAlbumPhotosAuth;
use App\Domain\Model\Authorization\GroupPicturesAuth;
use App\Domain\Model\Authorization\GroupVideosAuth;

use App\Domain\Model\Authorization\PagePostsAuth;
use App\Domain\Model\Authorization\PageAlbumPhotosAuth;
use App\Domain\Model\Authorization\PagePicturesAuth;
use App\Domain\Model\Authorization\PageVideosAuth;

use App\Application\Exceptions\ForbiddenException;
use App\Application\Exceptions\ValidationException;

class SharesService {
    
    private UserAlbumPhotoRepository $userAlbumPhotos;
    private ProfilePictureRepository $profilePictures;
    private UserVideoRepository $userVideos;
    private UserPostRepository $userPosts;
    
    private GroupAlbumPhotoRepository $groupAlbumPhotos;
    private GroupPictureRepository $groupPictures;
    private GroupVideoRepository $groupVideos;
    private GroupPostRepository $groupPosts;
    
    private PageAlbumPhotoRepository $pageAlbumPhotos;
    private PagePictureRepository $pagePictures;
    private PageVideoRepository $pageVideos;
    private PagePostRepository $pagePosts;
    
    private UserAlbumPhotosAuth $userAlbumPhotosAuth;
    private ProfilePicturesAuth $profilePicturesAuth;
    private UserVideosAuth $userVideosAuth;
    private UserPostsAuth $userPostsAuth;
            
    private GroupAlbumPhotosAuth $groupAlbumPhotosAuth;
    private GroupPicturesAuth $groupPicturesAuth;
    private GroupVideosAuth $groupVideosAuth;
    private GroupPostsAuth $groupPostsAuth;
            
    private PageAlbumPhotosAuth $pageAlbumPhotosAuth;
    private PagePicturesAuth $pagePicturesAuth;
    private PageVideosAuth $pageVideosAuth;
    private PagePostsAuth $pagePostsAuth;
    
    public function __construct(UserAlbumPhotoRepository $userAlbumPhotos, ProfilePictureRepository $profilePictures, UserVideoRepository $userVideos, UserPostRepository $userPosts, GroupAlbumPhotoRepository $groupAlbumPhotos, GroupPictureRepository $groupPictures, GroupVideoRepository $groupVideos, GroupPostRepository $groupPosts, PageAlbumPhotoRepository $pageAlbumPhotos, PagePictureRepository $pagePictures, PageVideoRepository $pageVideos, PagePostRepository $pagePosts, UserAlbumPhotosAuth $userAlbumPhotosAuth, ProfilePicturesAuth $profilePicturesAuth, UserVideosAuth $userVideosAuth, UserPostsAuth $userPostsAuth, GroupAlbumPhotosAuth $groupAlbumPhotosAuth, GroupPicturesAuth $groupPicturesAuth, GroupVideosAuth $groupVideosAuth, GroupPostsAuth $groupPostsAuth, PageAlbumPhotosAuth $pageAlbumPhotosAuth, PagePicturesAuth $pagePicturesAuth, PageVideosAuth $pageVideosAuth, PagePostsAuth $pagePostsAuth) {
        $this->userAlbumPhotos = $userAlbumPhotos;
        $this->profilePictures = $profilePictures;
        $this->userVideos = $userVideos;
        $this->userPosts = $userPosts;
        $this->groupAlbumPhotos = $groupAlbumPhotos;
        $this->groupPictures = $groupPictures;
        $this->groupVideos = $groupVideos;
        $this->groupPosts = $groupPosts;
        $this->pageAlbumPhotos = $pageAlbumPhotos;
        $this->pagePictures = $pagePictures;
        $this->pageVideos = $pageVideos;
        $this->pagePosts = $pagePosts;
        $this->userAlbumPhotosAuth = $userAlbumPhotosAuth;
        $this->profilePicturesAuth = $profilePicturesAuth;
        $this->userVideosAuth = $userVideosAuth;
        $this->userPostsAuth = $userPostsAuth;
        $this->groupAlbumPhotosAuth = $groupAlbumPhotosAuth;
        $this->groupPicturesAuth = $groupPicturesAuth;
        $this->groupVideosAuth = $groupVideosAuth;
        $this->groupPostsAuth = $groupPostsAuth;
        $this->pageAlbumPhotosAuth = $pageAlbumPhotosAuth;
        $this->pagePicturesAuth = $pagePicturesAuth;
        $this->pageVideosAuth = $pageVideosAuth;
        $this->pagePostsAuth = $pagePostsAuth;
    }

    
    // С расшариванием сущностей из групп и страниц не всё так просто. Возможно нельзя будет делиться сущностями, которые созданы от имени пользователя. В вк нельзя это делать, там есть
    // возможность поделиться такими сущностями, но это копирование, а не шаринг.
    // Я же думаю, что можно сделать полноценную возможность делиться такими сущностями. Я не знаю почему так не сделали в ВК, наверное из-за того, что им лень. Даже в фейсбуке так сделали
    
    function prepareShared(User $requester, string $type, string $id): Shared {
        $shareable = null;
        
        if($type === 'user-album-photo') { /* Фото должно быть доступным для $requester.
            // Не должно быть в корзине, метод PostsAuth::failIfCannotSee() не поможет, потому что владельцу доступно такое фото
            // Не должно быть из коммента, но это будет проверяться не здесь
            // Не должно быть временным, метод PostsAuth::failIfCannotSee() не поможет, потому что владельцу доступно такое фото
            // Получается, что если фото в корзине или связанная сущность в корзине или фото временное, то такое фото доступно владельцу. Но таким фото нельзя поделиться, поэтому метода
            // PostsAuth::failIfCannotSee() не достаточно
            // 
            // Но PostsAuth::failIfCannotSee() нужен в таких случаях
            // 1. Проверить доступность сущностей, с которыми может быть связано фото
            // 2. Проверить не удалён ли владелец
            // 3. Проверить активен ли владелец
            // 4. Доступен ли профиль владельца
             * 
             * Здесь есть проблема с авторизацией, здесь достаточно вызвать метод canSee(), но этот метод не такой как failIfCannotSee(), он не выбросит исключение с точной
             * причиной недоступности, он просто возвращает boolean значение, то есть true или false. Я пока не знаю нужно ли возвращать точную причину недоступности, если нужно
             * будет, то мне кажется, что хорошим вариантом будет специальный метод failIfCannotShare(), правда он будет идентичен методу failIfCannotSee() кроме сообщений.
            */ 
            $photo = $this->userAlbumPhotos->getById($id);
            if(!$photo || ($photo && $photo->owner()->isDeleted())) {
                throw new UnprocessableRequestException(124, "Cannot share photo $id, photo not found");
            }
            $this->userAlbumPhotosAuth->failIfCannotShare($requester, $photo);
            return new \App\Domain\Model\Common\Shares\SharedUserAlbumPhoto($photo);
        }
        elseif($type === 'profile-picture') {
            $picture = $this->profilePictures->getById($id);
            if(!$picture || ($picture && $picture->owner()->isDeleted())) {
                throw new UnprocessableRequestException(124, "Cannot share picture $id, picture not found");
            }
            $this->profilePicturesAuth->failIfCannotShare($requester, $picture);
            return new \App\Domain\Model\Common\Shares\SharedProfilePicture($picture);
        }
        elseif($type === 'user-post') {
            $post = $this->userPosts->getById($id);
            if(!$post || ($post && $post->creator()->isDeleted())) {
                throw new UnprocessableRequestException(124, "Cannot share post $id, post not found");
            }
            if(!$this->userPostsAuth->canSee($requester, $post)) {
                throw new UnprocessableRequestException(124, "Cannot share post $id, access is prohibited");
            }
            return new SharedUserPost($post);
        }
        elseif($type === 'user-video') {
            $video = $this->userVideos->getById($id);
            if(!$video || ($video && $video->owner()->isDeleted())) {
                throw new UnprocessableRequestException(124, "Cannot share video $id, video not found");
            }
            if(!$this->userVideosAuth->canSee($requester, $video)) {
                throw new UnprocessableRequestException(124, "Cannot share video $id, access is prohibited");
            }
            return new SharedUserVideo($video);
        }
        
        elseif($type === 'group-album-photo') {
            $photo = $this->groupAlbumPhotos->getById($id);
            if(!$photo || ($photo && $photo->owningGroup()->isDeleted())) {
                throw new UnprocessableRequestException(124, "Cannot share photo $id, photo not found");
            }
            if(!$this->groupAlbumPhotosAuth->canSee($requester, $photo)) {
                throw new UnprocessableRequestException(124, "Cannot share photo $id, access is prohibited");
            }
            return new SharedGroupAlbumPhoto($photo);
        }
        elseif($type === 'group-picture') {
            $picture = $this->groupPictures->getById($id);
            if(!$picture || ($picture && $picture->owningGroup()->isDeleted())) {
                throw new UnprocessableRequestException(124, "Cannot share picture $id, picture not found");
            }
            if(!$this->groupPicturesAuth->canSee($requester, $picture)) {
                throw new UnprocessableRequestException(124, "Cannot share picture $id, access is prohibited");
            }
            return new SharedGroupPicture($picture);
        }
        elseif($type === 'group-post') {
            $post = $this->groupPosts->getById($id);
            if(!$post || ($post && $post->owningGroup()->isDeleted())) {
                throw new UnprocessableRequestException(124, "Cannot share post $id, post not found");
            }
            if(!$this->groupPostsAuth->canSee($requester, $post)) {
                throw new UnprocessableRequestException(124, "Cannot share post $id, access is prohibited");
            }
            return new SharedGroupPost($post);
        }
        elseif($type === 'group-video') {
            $video = $this->groupVideos->getById($id);
            if(!$video || ($video && $video->owningGroup()->isDeleted())) {
                throw new UnprocessableRequestException(124, "Cannot share video $id, video not found");
            }
            if(!$this->groupVideosAuth->canSee($requester, $video)) {
                throw new UnprocessableRequestException(124, "Cannot share video $id, access is prohibited");
            }
            return new SharedGroupVideo($video);
        }
        elseif($type === 'page-album-photo') {
            $photo = $this->pageAlbumPhotos->getById($id);
            if(!$photo || ($photo && $photo->owningPage()->isDeleted())) {
                throw new UnprocessableRequestException(124, "Cannot share photo $id, photo not found");
            }
            if(!$this->pageAlbumPhotosAuth->canSee($requester, $photo)) {
                throw new UnprocessableRequestException(124, "Cannot share photo $id, access is prohibited");
            }
            return new SharedPageAlbumPhoto($photo);
        }
        
        elseif($type === 'page-picture') {
            $picture = $this->pagePictures->getById($id);
            if(!$picture || ($picture && $picture->owningPage()->isDeleted())) {
                throw new UnprocessableRequestException(124, "Cannot share picture $id, picture not found");
            }
            if(!$this->pagePicturesAuth->canSee($requester, $picture)) {
                throw new UnprocessableRequestException(124, "Cannot share picture $id, access is prohibited");
            }
            return new SharedPagePicture($picture);
        }
        elseif($type === 'page-post') {
            $post = $this->pagePosts->getById($id);
            if(!$post || ($post && $post->owningPage()->isDeleted())) {
                throw new UnprocessableRequestException(124, "Cannot share post $id, post not found");
            }
            if(!$this->pagePostsAuth->canSee($requester, $post)) {
                throw new UnprocessableRequestException(124, "Cannot share post $id, access is prohibited");
            }
            return new SharedPagePost($post);
        }
        elseif($type === 'page-video') {
            $video = $this->pageVideos->getById($id);
            if(!$video || ($video && $video->owningPage()->isDeleted())) {
                throw new UnprocessableRequestException(124, "Cannot share video $id, video not found");
            }
            if(!$this->pageVideosAuth->canSee($requester, $video)) {
                throw new UnprocessableRequestException(124, "Cannot share video $id, access is prohibited");
            }
            return new SharedPageVideo($video);
        }
        
        else {
            throw new ValidationException("Param shared contains incorrect type, only 'user-photo', 'user-video', 'user-post', 'group-photo', 'group-video', 'group-post', 'page-photo', 'page-video', 'page-post' are allowed ");
        }
    }
    
}