<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Comments;

interface Photo extends Attachment {
    function id(): string;
    function src(): string;
}
