<?php
declare(strict_types=1);
namespace App\DTO\Common;

class CommentedDTO {
    
    public string $type;
    public string $id;
    
    public function __construct(string $type, string $id) {
        $this->type = $type;
        $this->id = $id;
    }

}
