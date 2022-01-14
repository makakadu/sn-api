<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

//use App\Domain\Model\Users\Post\Post as ProfilePost;
//use App\Domain\Model\Groups\Post\Post as GroupPost;
//use App\Domain\Model\Pages\Post\Post as PagePost;
use App\Domain\Model\Users\Post\Post as ProfilePost;
use App\Domain\Model\Groups\Post\Post as GroupPost;
use App\Domain\Model\Pages\Post\Post as PagePost;

interface PostVisitor {
    function visitGroupPost(GroupPost $post);
    function visitProfilePost(ProfilePost $post);
    function visitPagePost(PagePost $post);
}