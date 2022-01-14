<?php
declare(strict_types=1);
namespace App\Application\Common\GetPosts;

use App\Application\BaseRequest;

class GetPostsRequest implements BaseRequest {
    public ?string $requesterId;
    public $text;
    public $offset;
    
    public $count;
    public $order;
    
    public $commentsCount;
    public $commentsOrder;
    public $commentsType;
    
    public $hideFromUsers;
    public $hideFromGroups;
    public $hideFromPages;
    
    function __construct(?string $requesterId, $text, $offset, $count, $order, $commentsCount, $commentsOrder, $commentsType, $hideFromUsers, $hideFromGroups, $hideFromPages) {
        $this->requesterId = $requesterId;
        $this->text = $text;
        $this->offset = $offset;
        $this->count = $count;
        $this->order = $order;
        $this->commentsCount = $commentsCount;
        $this->commentsOrder = $commentsOrder;
        $this->commentsType = $commentsType;
        $this->hideFromUsers = $hideFromUsers;
        $this->hideFromGroups = $hideFromGroups;
        $this->hideFromPages = $hideFromPages;
    }

    
}
