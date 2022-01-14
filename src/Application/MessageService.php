<?php

declare(strict_types=1);

namespace App\Application;

trait MessageService {
    /**
     * @Inject
     * @var \App\Domain\Model\Dialogue\MessageRepository
     */
    protected $messages;
}
