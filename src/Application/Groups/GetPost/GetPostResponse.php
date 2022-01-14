<?php
declare(strict_types=1);
namespace App\Application\Groups\GetPost;

use App\Domain\Model\Groups\Post\Post;

class GetPostResponse {

    public function __construct(Post $post) {
        if($post) {
            
        }
//        $this->id = $profilePost->id();
//        $this->photos = array();
//        $this->videos = array();
//        foreach ($groupPost->media() as $file) {
//            if(get_class($file) === \App\Domain\Model\Photos\Photo::class) {
//                $this->photos[] = $file->src();
//            } else {
//                $this->videos[] = 'lolkek.avi';
//            }
//        }
    }
}
