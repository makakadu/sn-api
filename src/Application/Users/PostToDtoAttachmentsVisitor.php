<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Domain\Model\Users\Photos\Photo;

class PostToDtoAttachmentsVisitor implements \App\Domain\Model\Users\Post\AttachmentVisitor {
    /**
     * @return mixed
     */
    public function visitPostPhoto(\App\Domain\Model\Users\Post\Photo\Photo $photo) {
        return [
            'type' => 'photo',
            'id' => $photo->id(),
            'medium' => $photo->medium()
        ];
    }
    
    /**
     * @return mixed
     */
    public function visitPostVideo(\App\Domain\Model\Users\Post\Video\Video $video) {
        return [];
    }

}