<?php
declare(strict_types=1);
namespace App\DTO\Users;

class ConnectionsListDTO implements \App\DTO\Common\DTO {
    
    public string $id;
    public string $name;
    public string $connected_users_ids;

    public function __construct(string $id, string $name, string $connected_users_ids) {
        $this->id = $id;
        $this->name = $name;
        $this->connected_users_ids = $connected_users_ids;
    }

}
