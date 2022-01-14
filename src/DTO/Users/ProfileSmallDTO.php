<?php
declare(strict_types=1);
namespace App\DTO\Users;

class ProfileSmallDTO {
    
    public string $id;
    public ?string $pictureSmall;
    public string $first_name;
    public string $last_name;
    
    function __construct(string $id, ?string $pictureSmall, string $first_name, string $last_name) {
        $this->id = $id;
        $this->pictureSmall = $pictureSmall;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
    }


}
