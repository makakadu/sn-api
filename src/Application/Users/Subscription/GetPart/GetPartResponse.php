<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\GetPart;

use App\Application\BaseResponse;
use App\DTO\Users\SubscriptionDTO;
use App\DTO\Users\ProfileDTO;

class GetPartResponse implements BaseResponse {
    
    /** @var array<int, ProfileDTO> $connections */
    public array $subscriptions;
    public int $allCount;
    public ?string $cursor;

    /** @param array<int, ProfileDTO> $connections */
    public function __construct(array $connections, int $allCount, ?string $cursor) {
        $this->subscriptions = $connections;
        $this->allCount = $allCount;
        $this->cursor = $cursor;
    }

}