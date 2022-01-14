<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Comments;

use App\Domain\Model\Pages\Photos\AlbumPhoto\Comment\Comment as AlbumPhotoComment;
use App\Domain\Model\Pages\Photos\PagePicture\Comment\Comment as PagePictureComment;
use App\Domain\Model\Pages\Post\Comment\Comment as PostComment;
use App\Domain\Model\Pages\Videos\Comment\Comment as VideoComment;

/**
 * @template T
 */
interface PageCommentVisitor {

     /**
     * @return T
     */
    function visitAlbumPhotoComment(AlbumPhotoComment $comment);

     /**
     * @return T
     */
    function visitPagePictureComment(PagePictureComment $comment);

     /**
     * @return T
     */
    function visitPostComment(PostComment $comment);

     /**
     * @return T
     */
    function visitVideoComment(VideoComment $comment);

}