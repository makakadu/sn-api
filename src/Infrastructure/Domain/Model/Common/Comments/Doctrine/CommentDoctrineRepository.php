<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Common\Comments\Doctrine;

use App\Domain\Model\Common\Comments\CommentRepository;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;

class CommentDoctrineRepository extends AbstractDoctrineRepository implements CommentRepository {
    
    protected string $entityClass = "";
    
    public function getById(string $id): ?\App\Domain\Model\Common\Comments\Comment {
        
    }
    
    public function getPartCreatedOnBehalfOfUserByCreatorId(string $id, ?string $offsetId, int $count, ?string $order = 'asc') {
        // Проблема этого запроса в том, что есть комменты, которые созданы от имени страницы, например, и такие комменты извлекать не нужно, поэтому, мне кажется, что нужно это
        // как-то обозначить. Запрашивать комменты, созданные от имени страницы нужно только когда нужно посмотреть список комментов, которые созданы от имени страницы.
        
        // Если не используется наследование всех типов комментов от одного типа, то есть проблема с 'count', я не знаю стоит ли ограничивать количество комментов в подзапросах или
        // лучше ограничить их в главном запросе. Я не знаю как это влияет на производительность. 
    }

}
