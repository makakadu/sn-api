<?php
declare(strict_types=1);
namespace App\Application\Users\GetContacts;

class GetContactsRequest implements \App\Application\BaseRequest {
    public ?string $requesterId;
    public string $userId;
    public $commonWith;
    public $cursor;
    public $count;

    public function __construct(?string $requesterId, string $userId, $commonWith, $cursor, $count) {
        $this->requesterId = $requesterId;
        $this->userId = $userId;
        $this->commonWith = $commonWith;
        $this->cursor = $cursor;
        $this->count = $count;
    }


}
