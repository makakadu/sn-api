<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Domain\Model\Common\Shares\SharedUserAlbumPhoto;
use App\Domain\Model\Common\Shares\SharedProfilePicture;
use App\Domain\Model\Common\Shares\SharedUserVideo;
use App\Domain\Model\Common\Shares\SharedUserPost;

use App\Domain\Model\Common\Shares\SharedGroupAlbumPhoto;
use App\Domain\Model\Common\Shares\SharedGroupPicture;
use App\Domain\Model\Common\Shares\SharedGroupVideo;
use App\Domain\Model\Common\Shares\SharedGroupPost;

use App\Domain\Model\Common\Shares\SharedPageAlbumPhoto;
use App\Domain\Model\Common\Shares\SharedPagePicture;
use App\Domain\Model\Common\Shares\SharedPageVideo;
use App\Domain\Model\Common\Shares\SharedPagePost;

/**
 * @template T
 */
interface SharedVisitor {
     /**
     * @return T
     */
    function visitSharedUserAlbumPhoto(SharedUserAlbumPhoto $shared);
     /**
     * @return T
     */
    function visitSharedProfilePicture(SharedProfilePicture $shared);
    /**
     * @return T
     */
    function visitSharedUserVideo(SharedUserVideo $shared);
    /**
     * @return T
     */
    function visitSharedUserPost(SharedUserPost $shared);
    /**
     * @return T
     */
    function visitSharedGroupAlbumPhoto(SharedGroupAlbumPhoto $shared);
    /**
     * @return T
     */
    function visitSharedGroupPicture(SharedGroupPicture $shared);
    /**
     * @return T
     */
    function visitSharedGroupVideo(SharedGroupVideo $shared);
    /**
     * @return T
     */
    function visitSharedGroupPost(SharedGroupPost $shared);
    /**
     * @return T
     */
    function visitSharedPageAlbumPhoto(SharedPageAlbumPhoto $shared);
    /**
     * @return T
     */
    function visitSharedPagePicture(SharedPagePicture $shared);
    /**
     * @return T
     */
    function visitSharedPageVideo(SharedPageVideo $shared);
    /**
     * @return T
     */
    function visitSharedPagePost(SharedPagePost $shared);
}