<?php
declare(strict_types=1);
namespace App\Application\Common\Reactions\GetReacted;

use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\Post;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Common\Shares\Shared;
use App\Domain\Model\Users\User\User;
use App\DTO\Users\UserPostDTO;
use App\DTO\Common\DTO;

class GetReactedResponse implements BaseResponse {
    
    /** @var array<int,DTO> $reacted */
    public array $reacted;

    /** @param array<int,DTO> $reacted */
    public function __construct(array $reacted) {
        $this->reacted = $reacted;
    }
}
