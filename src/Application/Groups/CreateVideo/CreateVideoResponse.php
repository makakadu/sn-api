<?php
declare(strict_types=1);
namespace App\Application\Groups\CreateVideo;

use App\Application\ApplicationService;
use App\Domain\Model\Users\Video\Video;
use App\Application\BaseResponse;

class CreateVideoResponse implements BaseResponse {
    
    public $responseMessage;
    
    function __construct(Video $video) {
        $this->responseMessage = 'ok';
    }
    
}