<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\DTO\CreatorDTO;
use App\DTO\Common\PhotoDTO;

class UnaccessibleProfilePictureDTO implements PhotoDTO {

    public string $id;
    public string $type;
    public ?CreatorDTO $creator;
    public int $timestamp;
    
    public function __construct(string $id, ?CreatorDTO $creator, int $timestamp) {
        $this->id = $id;
        $this->type = "profile_picture";
        $this->creator = $creator;
        $this->timestamp = $timestamp;
    }

}
