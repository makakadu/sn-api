<?php
declare(strict_types=1);
namespace App\DTO\Common;

class AnimationAttachmentDTO extends AttachmentDTO {
    public string $src;
    public string $preview;
    
    function __construct(string $id, string $src, string $preview) {
        $this->type = 'animation';
        $this->id = $id;
        $this->src = $src;
        $this->preview = $preview;
    }

}
