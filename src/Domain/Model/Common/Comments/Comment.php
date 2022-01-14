<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Comments;

use App\Domain\Model\Users\User\User;

interface Comment {
    const MAX_TEXT_LENGTH = 500;
    
    function id(): string;
    function text(): string;
    function creator(): User;
}
