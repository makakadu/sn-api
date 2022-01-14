<?php
declare(strict_types=1);
namespace App\Application\Pages\Page\Patch;

use App\Application\BaseRequest;

class PatchRequest implements BaseRequest {
    public string $requesterId;
    public string $pageId;
    /** @var mixed $payload */
    public $payload;

    /**
     * @param mixed $payload
     */
    public function __construct(string $requesterId, string $pageId, $payload) {
        $this->requesterId = $requesterId;
        $this->pageId = $pageId;
        $this->payload = $payload;
    }


}
