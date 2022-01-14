<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\DTO\Users\UserSmallDTO;

class ConnectionDTO implements \App\DTO\Common\DTO {
    
    public string $id;
//    public string $initiatorId;
//    public string $targetId;
    public bool $isAccepted;
    public UserSmallDTO $initiator;
    public UserSmallDTO $target;

    public function __construct(string $id, UserSmallDTO $initiator, UserSmallDTO $target, bool $is_accepted) {
        $this->id = $id;
        $this->initiator = $initiator;
        $this->target = $target;
        $this->isAccepted = $is_accepted;
    }

}
