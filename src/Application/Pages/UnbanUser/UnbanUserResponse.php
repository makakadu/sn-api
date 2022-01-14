<?php //
declare(strict_types=1);
namespace App\Application\Pages\UnbanUser;

use App\Application\BaseResponse;
use App\Application\MapPostsVisitor;
use App\Domain\Model\Common\Post;

class UnbanUserResponse implements BaseResponse {
    public array $news = [];
    public int $allNewsCount;
    
    public function __construct() {
//        $postVisitor = new MapPostsVisitor();
//        
//        foreach($news as $item) {
//            $item->accept($postVisitor);
//        }
//        $posts = $postVisitor->getPosts();
    }
}
