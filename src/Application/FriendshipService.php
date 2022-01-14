<?php

declare(strict_types=1);

namespace App\Application;

trait FriendshipService {
    /**
     * @Inject
     * @var \App\Domain\Model\Friendship\FriendshipRepository
     */
    protected $friendships;
}
