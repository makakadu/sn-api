<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Users\AlbumPhoto\AlbumPhoto as UserAlbumPhoto;
use App\Domain\Model\Users\Videos\Video as UserVideo;
use App\Domain\Model\Users\Post\Post as UserPost;

use App\Domain\Model\Authorization\UserPhotosAuth;
use App\Domain\Model\Authorization\UserVideosAuth;
use App\Domain\Model\Authorization\UserPostsAuth;

class SaveableAuth {
    use AuthorizationTrait;
    
    private UserAlbumPhotosAuth $userAlbumPhotosAuth;
    private UserVideosAuth $userVideosAuth;
    private UserPostsAuth $userPostsAuth;
    
    function canSeeUserAlbumPhoto(UserAlbumPhoto $photo): bool {
        return $this->userAlbumPhotosAuth->canSee($photo);
    }
    
    function canSeeUserPost(UserPost $post): bool {
        return $this->userPostsAuth->canSee($post);
    }
    
    function canSeeUserVideo(UserVideo $video): bool {
        return $this->userVideosAuth->canSee($video);
    }
    
    function userAlbumPhotosAuth(): UserAlbumPhotosAuth {
        return $this->userAlbumPhotosAuth;
    }

    function userVideosAuth(): UserVideosAuth {
        return $this->userVideosAuth;
    }

    function userPostsAuth(): UserPostsAuth {
        return $this->userPostsAuth;
    }
    
}
