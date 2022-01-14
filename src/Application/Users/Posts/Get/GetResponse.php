<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\Get;

use App\Application\BaseResponse;
use App\DTO\Users\UserPostDTO;

class GetResponse implements BaseResponse {
    
    public UserPostDTO $post;

    public function __construct(UserPostDTO $postDTO) {
        $this->post = $postDTO;
    }
}