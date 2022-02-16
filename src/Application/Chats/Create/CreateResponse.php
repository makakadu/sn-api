<?php //
declare(strict_types=1);
namespace App\Application\Chats\Create;

use App\Application\BaseResponse;

class CreateResponse implements BaseResponse {
    public string $id;
    public ?string $firstMessageId;
    
    function __construct(string $id, ?string $firstMessageId) {
        $this->id = $id;
        $this->firstMessageId = $firstMessageId;
    }
}
