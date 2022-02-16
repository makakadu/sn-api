<?php //
declare(strict_types=1);
namespace App\Application\Chats\CreateMessage;

use App\Application\BaseResponse;

class CreateMessageResponse implements BaseResponse {
    public string $id;
    
    function __construct(string $id) {
        $this->id = $id;
    }
}
