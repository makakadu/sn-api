<?php
declare(strict_types=1);
namespace App\Domain\Model\Users;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;

interface ProfileReactionInterface {
    function id(): string;
    function reactionType(): string;
    function creator(): User;
    function page(): ?Page;
}
