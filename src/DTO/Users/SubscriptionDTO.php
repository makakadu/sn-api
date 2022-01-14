<?php
declare(strict_types=1);
namespace App\DTO\Users;

class SubscriptionDTO implements \App\DTO\Common\DTO {
    
    public string $id;
    public UserSmallDTO $user;
    public UserSmallDTO $subscriber;
    public ?int $pauseDurationInDays;
    public ?int $pauseStart;
    public ?int $pauseEnd;

    public function __construct(string $id, UserSmallDTO $user, UserSmallDTO $subscriber, ?int $pauseDurationInDays, ?int $pauseStart, ?int $pauseEnd) {
        $this->id = $id;
        $this->user = $user;
        $this->subscriber = $subscriber;
        $this->pauseDurationInDays = $pauseDurationInDays;
        $this->pauseStart = $pauseStart;
        $this->pauseEnd = $pauseEnd;
    }

}
