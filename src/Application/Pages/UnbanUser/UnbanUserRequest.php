<?php //
declare(strict_types=1);
namespace App\Application\Pages\UnbanUser;

use App\Application\BaseRequest;

class UnbanUserRequest implements BaseRequest {
    public $requesterId;
    public $postId;
    
    public function __construct($requesterId, $postId) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
    }
}
