<?php
declare(strict_types=1);
namespace App\Domain\Model;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;

interface PageComment {
    function id(): string;
    function creator(): User;
    function page(): Page;
}
