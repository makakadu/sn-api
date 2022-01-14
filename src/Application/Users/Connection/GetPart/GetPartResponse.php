<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\GetPart;

use App\Application\BaseResponse;
use App\DTO\Users\UserPostDTO;
use App\DTO\Users\ConnectionDTO;

class GetPartResponse implements BaseResponse {
    
    /** @var array<int, ConnectionDTO> $connections */
    public array $connections;
    public int $allCount;
    public ?string $cursor;

    /** @param array<int, ConnectionDTO> $connections */
    public function __construct(array $connections, int $allCount, ?string $cursor) {
        $this->connections = $connections;
        $this->allCount = $allCount;
        $this->cursor = $cursor;
    }

}