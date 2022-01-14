<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection;

use App\Domain\Model\Users\SavesCollection\SavedItem;

use App\Domain\Model\Users\SavesCollection\ProfileItems\UserAlbumPhotoItem;
use App\Domain\Model\Users\SavesCollection\ProfileItems\ProfilePictureItem;
use App\Domain\Model\Users\SavesCollection\ProfileItems\UserVideoItem;
use App\Domain\Model\Users\SavesCollection\ProfileItems\UserPostItem;

use App\Domain\Model\Users\SavesCollection\GroupItems\GroupAlbumPhotoItem;
use App\Domain\Model\Users\SavesCollection\GroupItems\GroupPictureItem;
use App\Domain\Model\Users\SavesCollection\GroupItems\GroupVideoItem;
use App\Domain\Model\Users\SavesCollection\GroupItems\GroupPostItem;

use App\Domain\Model\Users\SavesCollection\PageItems\PageAlbumPhotoItem;
use App\Domain\Model\Users\SavesCollection\PageItems\PagePictureItem;
use App\Domain\Model\Users\SavesCollection\PageItems\PageVideoItem;
use App\Domain\Model\Users\SavesCollection\PageItems\PagePostItem;

/**
 * @template T
 */
interface SavedItemVisitor {
     /**
     * @return T
     */
    function visitUserAlbumPhotoItem(UserAlbumPhotoItem $item);
     /**
     * @return T
     */
    function visitProfilePictureItem(ProfilePictureItem $item);
    /**
     * @return T
     */
    function visitUserVideoItem(UserVideoItem $item);
    /**
     * @return T
     */
    function visitUserPostItem(UserPostItem $item);

    
    /**
     * @return T
     */
    function visitGroupAlbumPhotoItem(GroupAlbumPhotoItem $item);
    /**
     * @return T
     */
    function visitGroupPictureItem(GroupPictureItem $item);
    /**
     * @return T
     */
    function visitGroupVideoItem(GroupVideoItem $item);
    /**
     * @return T
     */
    function visitGroupPostItem(GroupPostItem $item);
    
    /**
     * @return T
     */
    function visitPageAlbumPhotoItem(PageAlbumPhotoItem $item);
    /**
     * @return T
     */
    function visitPagePictureItem(PagePictureItem $item);
    /**
     * @return T
     */
    function visitPageVideoItem(PageVideoItem $item);
    /**
     * @return T
     */
    function visitPagePostItem(PagePostItem $item);

}