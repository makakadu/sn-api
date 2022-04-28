<?php
declare(strict_types=1);
namespace App\DTO\Users;

abstract class ProfileDTO {
    
    public string $id;
    
    public function __construct(string $id) {
        $this->id = $id;

    }

}
