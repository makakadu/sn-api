<?php
declare(strict_types=1);
namespace App\Application;

trait PhotoService {
    /**
     * @Inject
     * @var \App\Domain\Model\Photos\Photo\PhotoRepository
     */
    protected $photos;
}