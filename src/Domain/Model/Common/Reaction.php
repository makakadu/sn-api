<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

use App\Domain\Model\Users\User\User;

//    const LIKE = 1;
//    const WOW = 2;
//    const HAHA = 3;
//    const SAD = 4;
//    const DISLIKE = 5;
//    const ANGRY = 6;
//    const SPUE = 7;
//    const CRINGE = 8;

/*
 * Я не знаю стоит ли делать список сущностей, на которые отреагировал пользователь. Вот плюсы:
 * 1. Можно удалить любую свою реакцию. Если списка не будет, то к некоторым сущностям нельзя будет получить доступ и значит реакцию невозможно будет удалить
 * 2. Это удобно, но есть сохранёнки, поэтому в этом нет смысла, по сути это дублирование функциональности
 * 
 * Минусы:
 * 1. Без наследования очень сложно запрашивать все типы реакции из БД в одном запросе.
 * 
 * 
 */

abstract class Reaction {
    
    protected User $creator;
    protected int $reactionType;
    protected \DateTime $createdAt;
    protected string $id;
    
    function creator(): User { // Возможно лучше возвращать объект типа Author?
        return $this->creator;
    }
    
    public function id(): string {
        return $this->id;
    }
    
    public function createdAt(): \DateTime {
        return $this->createdAt;
    }
    
    abstract function reacted(): Reactable;
    
    function getReactionType(): int { // get в имени метода нужен для Doctrine, иначе происходит ошибка из-за того, что свойство $reactionType protected
        return $this->reactionType;
    }
    
    function changeReactionType(int $type): void {
//        if($type < 1 || $type > 8) {
//            throw new \OutOfRangeException('Wrong reaction type');
//        }
        $this->reactionType = $type;
    }
    
    /** @return array<string> */
    static function reactionsTypes(): array {
        return (new \ReflectionClass(\App\Domain\Model\Common\ReactionsTypes::class))->getConstants();
    }
    
}
