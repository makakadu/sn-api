<?php
declare(strict_types=1);
namespace App\Application\Users\Cover\Create;

use App\Application\BaseRequest;
use Psr\Http\Message\UploadedFileInterface;

class CreateRequest implements BaseRequest {
    public string $requesterId;
    public UploadedFileInterface $uploadedPhoto;
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
    function __construct(string $requesterId, UploadedFileInterface $uploadedPhoto, $x, $y, $width) {
        $this->requesterId = $requesterId;
        $this->uploadedPhoto = $uploadedPhoto;
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
    }


}
