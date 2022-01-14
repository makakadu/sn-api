<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\UpdateRequest;

use App\Application\BaseRequest;

class UpdateRequest implements BaseRequest {
    public string $requesterId;
    public string $pictureId;
    /** @var mixed $x */
    public $x;
    /** @var mixed $y */
    public $y;
    /** @var mixed $width */
    public $width;
    
    /**
     * @param mixed $x
     * @param mixed $y
     * @param mixed $width
     */
    function __construct(string $requesterId, string $pictureId, $x, $y, $width) {
        $this->requesterId = $requesterId;
        $this->pictureId = $pictureId;
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
    }

}
