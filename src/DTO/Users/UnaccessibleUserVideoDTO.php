<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\DTO\CreatorDTO;
use App\DTO\Common\VideoDTO;

class UnaccessibleUserVideoDTO implements VideoDTO {

    public string $id;
    public string $type;
    public ?CreatorDTO $creator;
    public int $timestamp;
    
    public function __construct(string $id, ?CreatorDTO $creator, int $timestamp) {
        $this->id = $id;
        $this->type = "user_video";
        $this->creator = $creator;
        $this->timestamp = $timestamp;
    }

}
