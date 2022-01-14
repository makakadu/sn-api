<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Photos;

use App\Domain\Model\Users\Photos\AlbumPhoto\AlbumPhoto;
use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePicture;

/**
 * @template T
 */
interface PhotoVisitor {
    
     /**
     * @return T
     */
    function visitAlbumPhoto(AlbumPhoto $photo);
    
     /**
     * @return T
     */
    function visitProfilePicture(ProfilePicture $picture);
}