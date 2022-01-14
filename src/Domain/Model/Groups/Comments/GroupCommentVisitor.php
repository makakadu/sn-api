<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Comments;

use App\Domain\Model\Groups\Photos\AlbumPhoto\Comment\Comment as AlbumPhotoComment;
use App\Domain\Model\Groups\Photos\GroupPicture\Comment\Comment as GroupPictureComment;
use App\Domain\Model\Groups\Post\Comment\Comment as PostComment;
use App\Domain\Model\Groups\Videos\Comment\Comment as VideoComment;

/**
 * @template T
 */
interface GroupCommentVisitor {

     /**
     * @return T
     */
    function visitAlbumPhotoComment(AlbumPhotoComment $comment);

     /**
     * @return T
     */
    function visitGroupPictureComment(GroupPictureComment $comment);

     /**
     * @return T
     */
    function visitPostComment(PostComment $comment);

     /**
     * @return T
     */
    function visitVideoComment(VideoComment $comment);

}