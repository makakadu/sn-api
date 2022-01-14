<?php

declare(strict_types=1);

namespace App\Application;

trait PostService {
    /**
     * @Inject
     * @var \App\Domain\Model\Posts\UserPost\UserPostRepository
     */
    protected $profilePosts;
    /**
     * @Inject
     * @var \App\Domain\Model\Posts\GroupPost\GroupPostRepository
     */
    protected $groupPosts;
}