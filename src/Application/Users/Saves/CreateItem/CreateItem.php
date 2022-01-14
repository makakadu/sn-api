<?php
declare(strict_types=1);
namespace App\Application\Users\Saves\CreateItem;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
/*
use App\Domain\Model\Users\SavesCollection\ProfileItems\UserAlbumPhotoItem;
use App\Domain\Model\Users\SavesCollection\ProfileItems\UserAlbumPhotoCommentItem;

use App\Domain\Model\Users\SavesCollection\ProfileItems\ProfilePictureItem;
use App\Domain\Model\Users\SavesCollection\ProfileItems\ProfilePictureCommentItem;

use App\Domain\Model\Users\SavesCollection\ProfileItems\UserVideoItem;
use App\Domain\Model\Users\SavesCollection\ProfileItems\UserVideoCommentItem;

use App\Domain\Model\Users\SavesCollection\ProfileItems\UserPostItem;
use App\Domain\Model\Users\SavesCollection\ProfileItems\UserPostCommentItem;

use App\Domain\Model\Users\SavesCollection\GroupItems\GroupPhotoItem;
use App\Domain\Model\Users\SavesCollection\GroupItems\GroupAlbumPhotoCommentItem;

use App\Domain\Model\Users\SavesCollection\GroupItems\GroupPictureItem;
use App\Domain\Model\Users\SavesCollection\GroupItems\GroupPictureCommentItem;

use App\Domain\Model\Users\SavesCollection\GroupItems\GroupPostItem;
use App\Domain\Model\Users\SavesCollection\GroupItems\GroupPostCommentItem;

use App\Domain\Model\Users\SavesCollection\GroupItems\GroupVideoItem;
use App\Domain\Model\Users\SavesCollection\GroupItems\GroupVideoCommentItem;

use App\Domain\Model\Users\SavesCollection\PageItems\PostPostItem;
use App\Domain\Model\Users\SavesCollection\PageItems\PageAlbumPhotoCommentItem;

use App\Domain\Model\Users\SavesCollection\PageItems\PagePictureItem;
use App\Domain\Model\Users\SavesCollection\PageItems\PagePictureCommentItem;

use App\Domain\Model\Users\SavesCollection\PageItems\PagePostItem;
use App\Domain\Model\Users\SavesCollection\PageItems\PagePostCommentItem;

use App\Domain\Model\Users\SavesCollection\PageItems\PageVideoItem;
use App\Domain\Model\Users\SavesCollection\PageItems\PageVideoCommentItem;
*/

use App\Domain\Model\Users\User\UserRepository;

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

use App\Domain\Model\Users\Post\PostRepository as UserPostRepository;
use App\Domain\Model\Users\Photos\AlbumPhoto\AlbumPhotoRepository as UserAlbumPhotoRepository;
use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePictureRepository as UserProfilePictureRepository;
use App\Domain\Model\Users\Videos\VideoRepository as UserVideoRepository;

use App\Domain\Model\Groups\Post\PostRepository as GroupPostRepository;
use App\Domain\Model\Groups\Photos\AlbumPhoto\AlbumPhotoRepository as GroupAlbumPhotoRepository;
use App\Domain\Model\Groups\Photos\GroupPicture\GroupPictureRepository as GroupPictureRepository;
use App\Domain\Model\Groups\Videos\VideoRepository as GroupVideoRepository;

use App\Domain\Model\Pages\Post\PostRepository as PagePostRepository;
use App\Domain\Model\Pages\Photos\AlbumPhoto\AlbumPhotoRepository as PageAlbumPhotoRepository;
use App\Domain\Model\Pages\Photos\PagePicture\PagePictureRepository as PagePictureRepository;
use App\Domain\Model\Pages\Videos\VideoRepository as PageVideoRepository;

use App\Domain\Model\Users\SavesCollection\SavesCollectionRepository;
use App\Domain\Model\Saveable;

class CreateItem implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;

    private UserAlbumPhotosAuth $userAlbumPhotosAuth;
    private ProfilePicturesAuth $profilePicturesAuth;
    private UserPostsAuth $userPostsAuth;
    private UserVideosAuth $userVideosAuth;

    private PagePostsAuth $pagePostsAuth;
    private PageVideosAuth $pageVideosAuth;
    private PageAlbumPhotosAuth $pageAlbumPhotosAuth;
    private PagePicturesAuth $pagePicturesAuth;

    private GroupPostsAuth $groupPostsAuth;
    private GroupVideosAuth $groupVideosAuth;
    private GroupAlbumPhotosAuth $groupAlbumPhotosAuth;
    private GroupPicturesAuth $groupPicturesAuth;

    private UserPostRepository $userPosts;
    private UserAlbumPhotoRepository $userAlbumPhotos;
    private UserProfilePictureRepository $profilePictures;
    private UserVideoRepository $userVideos;

    private GroupPostRepository $groupPosts;
    private GroupAlbumPhotoRepository $groupAlbumPhotos;
    private GroupPictureRepository $groupPictures;
    private GroupVideoRepository $groupVideos;

    private PagePostRepository $pagePosts;
    private PageAlbumPhotoRepository $pageAlbumPhotos;
    private PagePictureRepository $pagePictures;
    private PageVideoRepository $pageVideos;
    
    private SavesCollectionRepository $savesCollections;
    
    public function __construct(SavesCollectionRepository $savesCollections, UserRepository $users, UserAlbumPhotosAuth $userAlbumPhotosAuth, ProfilePicturesAuth $profilePicturesAuth, UserPostsAuth $userPostsAuth, UserVideosAuth $userVideosAuth, PagePostsAuth $pagePostsAuth, PageVideosAuth $pageVideosAuth, PageAlbumPhotosAuth $pageAlbumPhotosAuth, PagePicturesAuth $pagePicturesAuth, GroupPostsAuth $groupPostsAuth, GroupVideosAuth $groupVideosAuth, GroupAlbumPhotosAuth $groupAlbumPhotosAuth, GroupPicturesAuth $groupPicturesAuth, UserPostRepository $userPosts, UserAlbumPhotoRepository $userAlbumPhotos, UserProfilePictureRepository $profilePictures, UserVideoRepository $userVideos, GroupPostRepository $groupPosts, GroupAlbumPhotoRepository $groupAlbumPhotos, GroupPictureRepository $groupPictures, GroupVideoRepository $groupVideos, PagePostRepository $pagePosts, PageAlbumPhotoRepository $pageAlbumPhotos, PagePictureRepository $pagePictures, PageVideoRepository $pageVideos) {
        $this->savesCollections = $savesCollections;
        $this->users = $users;
        $this->userAlbumPhotosAuth = $userAlbumPhotosAuth;
        $this->profilePicturesAuth = $profilePicturesAuth;
        $this->userPostsAuth = $userPostsAuth;
        $this->userVideosAuth = $userVideosAuth;
        $this->pagePostsAuth = $pagePostsAuth;
        $this->pageVideosAuth = $pageVideosAuth;
        $this->pageAlbumPhotosAuth = $pageAlbumPhotosAuth;
        $this->pagePicturesAuth = $pagePicturesAuth;
        $this->groupPostsAuth = $groupPostsAuth;
        $this->groupVideosAuth = $groupVideosAuth;
        $this->groupAlbumPhotosAuth = $groupAlbumPhotosAuth;
        $this->groupPicturesAuth = $groupPicturesAuth;
        $this->userPosts = $userPosts;
        $this->userAlbumPhotos = $userAlbumPhotos;
        $this->profilePictures = $profilePictures;
        $this->userVideos = $userVideos;
        $this->groupPosts = $groupPosts;
        $this->groupAlbumPhotos = $groupAlbumPhotos;
        $this->groupPictures = $groupPictures;
        $this->groupVideos = $groupVideos;
        $this->pagePosts = $pagePosts;
        $this->pageAlbumPhotos = $pageAlbumPhotos;
        $this->pagePictures = $pagePictures;
        $this->pageVideos = $pageVideos;
    }
        
    public function execute(BaseRequest $request): BaseResponse {
        //$this->validateRequest($request);
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $collection = $this->savesCollections->getById($request->collectionId);
        if(!$collection) {
            throw new \App\Application\Exceptions\NotExistException("Collection not found");
        }
        if(!$collection->creator()->equals($requester)) {
            throw new ForbiddenException(\App\Application\Errors::NO_RIGHTS, "Cannot add to collection of another user");
        }
        
        $saveableType = $request->type;
        $saveableId = $request->id;
        $saveable = null;
        $item = null;

        if($saveableType === 'user-album-photo') {
            $saveable = $this->userAlbumPhotos->getById($saveableId);
            $this->failIfSaveableNotFound($saveable, "User album photo $saveableId not found");
            $this->userAlbumPhotosAuth->failIfCannotSee($requester, $saveable);
            $item = new UserAlbumPhotoItem($saveable);
        }
        elseif($saveableType === 'profile-picture') {
            $saveable = $this->profilePictures->getById($saveableId);
            $this->failIfSaveableNotFound($saveable, "Profile picture $saveableId not found");
            $this->profilePicturesAuth->failIfCannotSee($requester, $saveable);
            $item = new ProfilePictureItem($saveable);
        }
        elseif($saveableType === 'user-video') {
            $saveable = $this->userVideos->getById($saveableId);
            $this->failIfSaveableNotFound($saveable, "User video $saveableId not found");
            $this->userVideosAuth->failIfCannotSee($requester, $saveable);
            $item = new \App\Domain\Model\Users\SavesCollection\ProfileItems\UserVideoItem($collection, $saveable);
        }
        elseif($saveableType === 'user-post') {
            $saveable = $this->userPosts->getById($saveableId);
            $this->failIfSaveableNotFound($saveable, "User post $saveableId not found");
            $this->userPostsAuth->failIfCannotSave($requester, $saveable);
            $item = new \App\Domain\Model\Users\SavesCollection\ProfileItems\UserPostItem($collection, $saveable);
        }
        elseif($saveableType === 'group-album-photo') {
            $saveable = $this->groupAlbumPhotos->getById($saveableId);
            $this->failIfSaveableNotFound($saveable, "Group album photo $saveableId not found");
            $this->groupAlbumPhotosAuth->failIfCannotSee($requester, $saveable);
            $item = new GroupPhotoItem($saveable);
        }
        elseif($saveableType === 'group-picture') {
            $saveable = $this->profilePictures->getById($saveableId);
            $this->failIfSaveableNotFound($saveable, "Profile picture $saveableId not found");
            $this->profilePicturesAuth->failIfCannotSee($requester, $saveable);
            $item = new GroupPictureItem($saveable);
        }
        elseif($saveableType === 'group-video') {
            $saveable = $this->groupVideos->getById($saveableId);
            $this->failIfSaveableNotFound($saveable, "Group video $saveableId not found");
            $this->groupVideosAuth->failIfCannotSee($requester, $saveable);
            $item = new GroupVideoItem($saveable);
        }
        elseif($saveableType === 'group-post') {
            $saveable = $this->groupVideos->getById($saveableId);
            $this->failIfSaveableNotFound($saveable, "Group post $saveableId not found");
            $this->groupPostsAuth->failIfCannotSee($requester, $saveable);
            $item = new GroupPostItem($saveable);
        }
        elseif($saveableType === 'page-album-photo') {
            $saveable = $this->pageAlbumPhotos->getById($saveableId);
            $this->failIfSaveableNotFound($saveable, "Page album photo $saveableId not found");
            $this->pageAlbumPhotosAuth->failIfCannotSee($requester, $saveable);
            $item = new PostPostItem($saveable);
        }
        elseif($saveableType === 'page-picture') {
            $saveable = $this->profilePictures->getById($saveableId);
            $this->failIfSaveableNotFound($saveable, "Page picture $saveableId not found");
            $this->profilePicturesAuth->failIfCannotSee($requester, $saveable);
            $item = new PagePictureItem($saveable);
        }
        elseif($saveableType === 'page-video') {
            $saveable = $this->pageVideos->getById($saveableId);
            $this->failIfSaveableNotFound($saveable, "Page video $saveableId not found");
            $this->pageVideosAuth->failIfCannotSee($requester, $saveable);
            $item = new PageVideoItem($saveable);
        }
        elseif($saveableType === 'page-post') {
            $saveable = $this->pageVideos->getById($saveableId);
            $this->failIfSaveableNotFound($saveable, "Page post $saveableId not found");
            $this->pagePostsAuth->failIfCannotSee($requester, $saveable);
            $item = new PagePostItem($saveable);
        }
        $collection->addItem($item);
        
        //exit;
        return new CreateItemResponse("OK");
    }
    
    private function failIfSaveableNotFound(?Saveable $saveable, string $message): void {
        if(!$saveable) {
            throw new \App\Application\Exceptions\NotExistException($message);
        }
    }
    
    private function validateRequest(CreateRequest $request): void {     
        $this->validateParamText($request->text);
        $this->validateParamDisableComments($request->disableComments);
        $this->validateParamIsPublic($request->isPublic);
    }
}
