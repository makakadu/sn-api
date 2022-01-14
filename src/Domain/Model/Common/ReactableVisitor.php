<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

use App\Domain\Model\Users\Post\Post as UserPost;
use App\Domain\Model\Users\Photos\AlbumPhoto\AlbumPhoto as UserAlbumPhoto;
use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePicture;
use App\Domain\Model\Users\Videos\Video as UserVideo;
use App\Domain\Model\Users\Comments\ProfileComment;

use App\Domain\Model\Groups\Post\Post as GroupPost;
use App\Domain\Model\Groups\Photos\AlbumPhoto\AlbumPhoto as GroupAlbumPhoto;
use App\Domain\Model\Groups\Photos\GroupPicture\GroupPicture;
use App\Domain\Model\Groups\Videos\Video as GroupVideo;
use App\Domain\Model\Groups\Comments\GroupComment;

use App\Domain\Model\Pages\Post\Post as PagePost;
use App\Domain\Model\Pages\Photos\AlbumPhoto\AlbumPhoto as PageAlbumPhoto;
use App\Domain\Model\Pages\Photos\PagePicture\PagePicture;
use App\Domain\Model\Pages\Videos\Video as PageVideo;
use App\Domain\Model\Pages\Comments\PageComment;

/**
 * @template T
 */
interface ReactableVisitor {
     /**
     * @return T
     */
    function visitUserPost(UserPost $post);
     /**
     * @return T
     */
    function visitUserAlbumPhoto(UserAlbumPhoto $photo);
     /**
     * @return T
     */
    function visitProfilePicture(ProfilePicture $picture);
     /**
     * @return T
     */
    function visitUserVideo(UserVideo $video);
     /**
     * @return T
     */
    function visitProfileComment(ProfileComment $comment);
    
    
     /**
     * @return T
     */
    function visitGroupPost(GroupPost $post);
     /**
     * @return T
     */
    function visitGroupAlbumPhoto(GroupAlbumPhoto $photo);
     /**
     * @return T
     */
    function visitGroupPicture(GroupPicture $picture);
     /**
     * @return T
     */
    function visitGroupVideo(GroupVideo $video);
     /**
     * @return T
     */
    function visitGroupComment(GroupComment $comment);
    
     /**
     * @return T
     */
    function visitPagePost(PagePost $post);
     /**
     * @return T
     */
    function visitPageAlbumPhoto(PageAlbumPhoto $photo);
     /**
     * @return T
     */
    function visitPagePicture(PagePicture $picture);
     /**
     * @return T
     */
    function visitPageVideo(PageVideo $video);
     /**
     * @return T
     */
    function visitPageComment(PageComment $comment);
    
}