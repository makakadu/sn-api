<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts;

use App\Domain\Model\Pages\Post\PostRepository;
use App\Domain\Model\Pages\Page\PageRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Post\Post;

abstract class PostAppService extends \App\Application\PageAppService {
    
    protected PostRepository $posts;

    function __construct(UserRepository $users, PageRepository $pages, PostRepository $posts) {
        parent::__construct($users, $pages);
        $this->posts = $posts;
    }

    function findPostOrFail(string $postId, bool $asTarget): ?Post {
        $post = $this->posts->getById($postId);
        
        $found = true;
        if(!$post) {
            $found = false;
        }
        if(!$found && $asTarget) {
            throw new NotExistException("Photo $postId not found");
        } elseif(!$found && !$asTarget) {
            throw new UnprocessableRequestException("Photo $postId not found");
        }
        return $post;
    }

}