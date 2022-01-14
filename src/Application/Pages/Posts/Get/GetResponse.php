<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\Get;

use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\Post;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Common\Shares\Shared;
use App\Domain\Model\Users\User\User;
use App\DTO\Users\UserPostDTO;
use App\DataTransformer\Users\PostTransformer;

class GetResponse implements BaseResponse {
    
    public UserPostDTO $postDTO;

    public function __construct(UserPostDTO $postDTO) {
        $this->postDTO = $postDTO;
    }
}