<?php
declare(strict_types=1);
namespace App\Application\Common\GetPosts;

use App\Application\BaseResponse;

class GetPostsResponse implements BaseResponse {
    public array $news = [];
    public int $allNewsCount;
    
    public function __construct(array $news) {
//        $postVisitor = new MapPostsVisitor();
//        
//        foreach($news as $item) {
//            $item->accept($postVisitor);
//        }
//        $posts = $postVisitor->getPosts();
    }
}
