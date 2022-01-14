<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetPosts;

use App\Domain\Model\Users\Post\Post;
use App\DTO\Users\UserPostDTO;

class GetPostsResponse implements \App\Application\BaseResponse {
    /** @var array<int, PostDTO> $items */
    public array $items;
    
    /** @param array<Post> $posts */
    public function __construct(array $posts) {
        $transformer = new \App\Application\Users\PostTransformer();
        foreach ($posts as $post) {
            $this->items[] = $transformer->transformOne($post, 3, 'root', 'ASC');
        }
    }
}
