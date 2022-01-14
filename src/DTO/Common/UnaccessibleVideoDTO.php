<?php
declare(strict_types=1);
namespace App\DTO\Common;

class UnaccessibleVideoDTO implements \App\DTO\Common\VideoDTO {

    public string $id;
    public string $type;
    
    public function __construct(string $id, string $type) {
        $this->id = $id;
        $this->type = $type;
    }

}
