<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Post;

interface PostAttachment {
    function id(): string;
    
    /**
     * @template T
     * @param PostAttachmentVisitor <T> $visitor
     * @return T
     */
    function acceptPostAttachmentVisitor(PostAttachmentVisitor $visitor);
}
