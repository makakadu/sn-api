<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Post\Doctrine;

use App\Domain\Model\Users\Post\Comment\Reply;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\Post\Comment\ReplyRepository;

class ReplyDoctrineRepository extends AbstractDoctrineRepository implements ReplyRepository {
    protected $entityClass = Reply::class;
}
