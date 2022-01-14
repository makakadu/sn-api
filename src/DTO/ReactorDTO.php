<?php
declare(strict_types=1);
namespace App\DTO;

class ReactorDTO {
    
    public string $id;
    public string $type;
    public ?string $pictureSmall;
    
    function __construct(string $id, string $type, ?string $pictureSmall) {
        $this->id = $id;
        $this->type = $type;
        $this->pictureSmall = $pictureSmall;
    }


}
