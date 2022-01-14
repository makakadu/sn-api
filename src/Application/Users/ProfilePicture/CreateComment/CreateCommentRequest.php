<?php //
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\CreateComment;

use App\Application\BaseRequest;

class CreateCommentRequest implements BaseRequest {
    public string $requesterId;
    public string $photoId;
    /** @var mixed $text */
    public $text;
    /** @var mixed $replied */
    public $replied;
    /** @var mixed $attachment */
    public $attachment;
    
    /**
     * @param mixed $text
     * @param mixed $replied
     * @param mixed $attachment
     */
    function __construct(string $requesterId, string $photoId, $text, $replied, $attachment) {
        $this->requesterId = $requesterId;
        $this->photoId = $photoId;
        $this->text = $text;
        $this->replied = $replied;
        $this->attachment = $attachment;
    }

}
