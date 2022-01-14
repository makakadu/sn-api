<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Comments;

interface Attachment {
    function id(): string;
    function preview(): string;
}
