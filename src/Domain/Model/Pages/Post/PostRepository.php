<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Post;

use App\Domain\Model\Pages\Page\Page;
use App\Domain\Repository;

interface PostRepository extends Repository {
    function getById(string $id): ?Post;
    
    /** @return array<int, Post> */
    function getPartOfNotDeletedByPage(Page $page, ?string $offsetId, int $count): array;
}
