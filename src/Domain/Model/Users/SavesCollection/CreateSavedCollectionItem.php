<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection;

/*
 * Пока что этот класс не нужен, создание происходит в другом месте
 */

//use App\Domain\Model\Users\SavesCollection\SavedItem;
//
//use App\Domain\Model\Users\Photos\AlbumPhoto\AlbumPhoto as UserAlbumPhoto;
//use App\Domain\Model\Users\SavesCollection\ProfileItems\UserAlbumPhotoItem;
//use App\Domain\Model\Users\Photos\AlbumPhoto\Comment\Comment as UserAlbumPhotoComment;
//use App\Domain\Model\Users\SavesCollection\ProfileItems\UserAlbumPhotoCommentItem;
//
//use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePicture;
//use App\Domain\Model\Users\SavesCollection\ProfileItems\ProfilePictureItem;
//use App\Domain\Model\Users\Photos\ProfilePicture\Comment\Comment as ProfilePictureComment;
//use App\Domain\Model\Users\SavesCollection\ProfileItems\ProfilePictureCommentItem;
//
//use App\Domain\Model\Users\Videos\Video as UserVideo;
//use App\Domain\Model\Users\SavesCollection\ProfileItems\UserVideoItem;
//use App\Domain\Model\Users\Videos\Comment\Comment as UserVideoComment;
//use App\Domain\Model\Users\SavesCollection\ProfileItems\UserVideoCommentItem;
//
//use App\Domain\Model\Users\Post\Post as UserPost;
//use App\Domain\Model\Users\SavesCollection\ProfileItems\UserPostItem;
//use App\Domain\Model\Users\Post\Comment\Comment as UserPostComment;
//use App\Domain\Model\Users\SavesCollection\ProfileItems\UserPostCommentItem;
//
//use App\Domain\Model\Groups\Photos\AlbumPhoto\AlbumPhoto as GroupAlbumPhoto;
//use App\Domain\Model\Users\SavesCollection\GroupItems\GroupPhotoItem;
//use App\Domain\Model\Groups\Photos\AlbumPhoto\Comment\Comment as GroupAlbumPhotoComment;
//use App\Domain\Model\Users\SavesCollection\GroupItems\GroupAlbumPhotoCommentItem;
//
//use App\Domain\Model\Groups\Photos\GroupPicture\GroupPicture;
//use App\Domain\Model\Users\SavesCollection\GroupItems\GroupPictureItem;
//use App\Domain\Model\Groups\Photos\GroupPicture\Comment\Comment as GroupPictureComment;
//use App\Domain\Model\Users\SavesCollection\GroupItems\GroupPictureCommentItem;
//
//use App\Domain\Model\Groups\Post\Post as GroupPost;
//use App\Domain\Model\Users\SavesCollection\GroupItems\GroupPostItem;
//use App\Domain\Model\Groups\Post\Comment\Comment as GroupPostComment;
//use App\Domain\Model\Users\SavesCollection\GroupItems\GroupPostCommentItem;
//
//use App\Domain\Model\Groups\Videos\Video as GroupVideo;
//use App\Domain\Model\Users\SavesCollection\GroupItems\GroupVideoItem;
//use App\Domain\Model\Groups\Videos\Comment\Comment as GroupVideoComment;
//use App\Domain\Model\Users\SavesCollection\GroupItems\GroupVideoCommentItem;
//
//use App\Domain\Model\Pages\Photos\AlbumPhoto\AlbumPhoto as PageAlbumPhoto;
//use App\Domain\Model\Users\SavesCollection\PageItems\PostPostItem;
//use App\Domain\Model\Pages\Photos\AlbumPhoto\Comment\Comment as PageAlbumPhotoComment;
//use App\Domain\Model\Users\SavesCollection\PageItems\PageAlbumPhotoCommentItem;
//
//use App\Domain\Model\Pages\Photos\PagePicture\PagePicture;
//use App\Domain\Model\Users\SavesCollection\PageItems\PagePictureItem;
//use App\Domain\Model\Pages\Photos\PagePicture\Comment\Comment as PagePictureComment;
//use App\Domain\Model\Users\SavesCollection\PageItems\PagePictureCommentItem;
//
//use App\Domain\Model\Pages\Post\Post as PagePost;
//use App\Domain\Model\Users\SavesCollection\PageItems\PagePostItem;
//use App\Domain\Model\Pages\Post\Comment\Comment as PagePostComment;
//use App\Domain\Model\Users\SavesCollection\PageItems\PagePostCommentItem;
//
//use App\Domain\Model\Pages\Videos\Video as PageVideo;
//use App\Domain\Model\Users\SavesCollection\PageItems\PageVideoItem;
//use App\Domain\Model\Pages\Videos\Comment\Comment as PageVideoComment;
//use App\Domain\Model\Users\SavesCollection\PageItems\PageVideoCommentItem;
//use App\Domain\Model\SaveableVisitor;

class CreateSavedCollectionItem { // implements SaveableVisitor {
//    
//    function visitUserAlbumPhoto(UserAlbumPhoto $saveable) {
//        return new UserAlbumPhotoItem($saveable);
//    }
//    
//    function visitUserAlbumPhotoComment(UserAlbumPhotoComment $saveable) {
//        return new UserAlbumPhotoCommentItem($saveable);
//    }
//    
//    function visitProfilePicture(ProfilePicture $saveable) {
//        return new ProfilePictureItem($saveable);
//    }
//    
//    function visitProfilePictureComment(ProfilePictureComment $saveable) {
//        return new ProfilePictureCommentItem($saveable);
//    }
//
//    function visitUserVideo(UserVideo $saveable) {
//        return new UserVideoItem($saveable);
//    }
//    
//    function visitUserVideoComment(UserVideoComment $saveable) {
//        return new UserVideoCommentItem($saveable);
//    }
//
//    function visitUserPost(UserPost $saveable) {
//        return new UserPostItem($saveable);
//    }
//
//    function visitUserPostComment(UserPostComment $saveable) {
//        return new UserPostCommentItem($saveable);
//    }
//
//    function visitGroupAlbumPhoto(GroupAlbumPhoto $saveable) {
//        return new GroupPhotoItem($saveable);
//    }
//
//    function visitGroupAlbumPhotoComment(GroupAlbumPhotoComment $saveable) {
//        return new GroupAlbumPhotoCommentItem($saveable);
//    }
//
//    function visitGroupPicture(GroupPicture $saveable) {
//        return new GroupPictureItem($saveable);
//    }
//    
//    function visitGroupPictureComment(GroupPictureComment $saveable) {
//        return new GroupPictureCommentItem($saveable);
//    }
//
//    function visitGroupVideo(GroupVideo $saveable) {
//        return new GroupVideoItem($saveable);
//    }
//
//    function visitGroupVideoComment(GroupVideoComment $saveable) {
//        return new GroupVideoCommentItem($saveable);
//    }
//
//    function visitGroupPost(GroupPost $saveable) {
//        return new GroupPostItem($saveable);
//    }
//
//    function visitGroupPostComment(GroupPostComment $saveable) {
//        return new GroupPostCommentItem($saveable);
//    }
//
//    function visitPageAlbumPhoto(PageAlbumPhoto $saveable) {
//        return new PostPostItem($saveable);
//    }
//
//    function visitPageAlbumPhotoComment(PageAlbumPhotoComment $saveable) {
//        return new PageAlbumPhotoCommentItem($saveable);
//    }
//
//    function visitPagePicture(PagePicture $saveable) {
//        return new PagePictureItem($saveable);
//    }
//
//    function visitPagePictureComment(PagePictureComment $saveable) {
//        return new PagePictureCommentItem($saveable);
//    }
//
//    function visitPageVideo(PageVideo $saveable) {
//        return new PageVideoItem($saveable);
//    }
//
//    function visitPageVideoComment(PageVideoComment $saveable) {
//        return new PageVideoCommentItem($saveable);
//    }
//
//    function visitPagePost(PagePost $saveable) {
//        return new PagePostItem($saveable);
//    }
//
//    function visitPagePostComment(PagePostComment $saveable) {
//        return new PagePostCommentItem($saveable);
//    }
}