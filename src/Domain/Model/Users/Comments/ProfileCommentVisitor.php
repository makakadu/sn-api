<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Comments;

use App\Domain\Model\Users\Photos\AlbumPhoto\Comment\Comment as AlbumPhotoComment;
use App\Domain\Model\Users\Photos\ProfilePicture\Comment\Comment as ProfilePictureComment;
use App\Domain\Model\Users\Post\Comment\Comment as PostComment;
use App\Domain\Model\Users\Videos\Comment\Comment as VideoComment;


/**
 * @template T
 */
interface ProfileCommentVisitor {

     /**
     * @return T
     */
    function visitAlbumPhotoComment(AlbumPhotoComment $comment);

     /**
     * @return T
     */
    function visitProfilePictureComment(ProfilePictureComment $comment);

     /**
     * @return T
     */
    function visitPostComment(PostComment $comment);

     /**
     * @return T
     */
    function visitVideoComment(VideoComment $comment);

}