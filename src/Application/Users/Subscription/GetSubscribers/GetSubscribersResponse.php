<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\GetSubscribers;

use App\Application\BaseResponse;
use App\DTO\Users\SubscriptionDTO;
use App\DTO\Users\ProfileDTO;

class GetSubscribersResponse implements BaseResponse {
    
    /** @var array<int, ProfileDTO> $subscribers */
    public array $subscribers;
    public int $allCount;
    public ?string $cursor;

    /** @param array<int, ProfileDTO> $subscribers */
    public function __construct(array $subscribers, int $allCount, ?string $cursor) {
        $this->subscribers = $subscribers;
        $this->allCount = $allCount;
        $this->cursor = $cursor;
    }

}