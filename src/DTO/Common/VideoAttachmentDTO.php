<?php
declare(strict_types=1);
namespace App\DTO\Common;

class VideoAttachmentDTO extends AttachmentDTO {
    public string $link;
    public string $preview;
    
    function __construct(string $id, string $link, string $preview) {
        $this->type = 'video';
        $this->id = $id;
        $this->link = $link;
        $this->preview = $preview;
    }

}
