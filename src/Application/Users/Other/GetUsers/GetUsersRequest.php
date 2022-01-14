<?php
declare(strict_types=1);
namespace App\Application\Users\GetUsers;

class GetUsersRequest {
    public string $requesterId;
    public ?int $page;
    public ?int $count;
    public ?string $ids;
    public ?string $username;

    public function __construct(string $requesterId, ?int $page, ?int $count, ?string $ids, ?string $username) {
        //echo $requesterId;exit();
        $this->requesterId = $requesterId;
        $this->page = $page;
        $this->count = $count;
        $this->ids = $ids;
        $this->username = $username;
    }
}
