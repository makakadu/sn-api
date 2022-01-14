<?php
declare(strict_types=1);
namespace App\Application\Users\Posts;

use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Users\Post\Post;
use App\Application\Exceptions\NotExistException;
use App\Application\Exceptions\UnprocessableRequestException;

trait PostAppServiceTrait {
    
    protected PostRepository $posts;

    function findPostOrFail(string $postId, bool $asTarget): ?Post {
        $post = $this->posts->getById($postId);
        
        $found = true;
        if(!$post) {
            $found = false;
        }
        if(!$found && $asTarget) {
            throw new NotExistException("Post $postId not found");
        } elseif(!$found && !$asTarget) {
            throw new UnprocessableRequestException("Post $postId not found");
        }
        return $post;
    }

}