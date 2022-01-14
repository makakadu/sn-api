<?php
declare(strict_types=1);
namespace App\DTO\Users;

class GroupDTO {
    
    public string $id;
    public ?string $pictureSmall;
    public string $name;
    
    function __construct(string $id, ?string $pictureSmall, string $name) {
        $this->id = $id;
        $this->pictureSmall = $pictureSmall;
        $this->name = $name;
    }


}
