<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

interface PhotoVisitor {
    /** @return mixed */
    function visitUserPhoto(\App\Domain\Model\Users\Photos\Photo $photo);
    /** @return mixed */
    function visitGroupPhoto(\App\Domain\Model\Groups\Photos\Photo $photo);
    /** @return mixed */
    function visitPagePhoto(\App\Domain\Model\Pages\Photos\Photo $photo);
    
//    function visitUserCommentPhoto(UserCommentPhoto $photo);
//    function visitUserPostPhoto(UserPostPhoto $photo);
//    function visitUserAlbumPhoto(UserAlbumPhoto $photo);
//    function visitUserPicture(UserPicture $picture);
//    
//    function visitGroupCommentPhoto(GroupCommentPhoto $photo);
//    function visitGroupPostPhoto(GroupPostPhoto $photo);
//    function visitGroupAlbumPhoto(GroupAlbumPhoto $photo);
//    function visitGroupPicture(GroupPicture $picture);
//    
//    function visitPageCommentPhoto(PageCommentPhoto $photo);
//    function visitPagePostPhoto(PagePostPhoto $photo);
//    function visitPageAlbumPhoto(PageAlbumPhoto $photo);
//    function visitPagePicture(PagePicture $picture);
    
}