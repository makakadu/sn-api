<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Membership;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Group\Group;

/*
 * Пригласить в группу могут только менеджеры
 * Если хочется пригласить кого-то в группу, то можно поделиться группой у себя на стенке, на стенке группы, на стенке страницы, в личном сообщении
 * Если пользователь хочет стать участником группы, то он может подать заявку
 * 
 * Здесь нет информации о роли в группе, то есть о том, является ли участник менеджером группы, например, админом, редактором или модератором, потому что быть менеджером - не
 * значит быть участником.
 * 
 * Если пользователь является админом, редактором или модератором, то подтверждение не нужно
 * 
 * В фейсбуке есть возможность в дальнейшем запретить приглашение в группу. Я не знаю как они это реализовали, но мне кажется, что они просто не удаляют Membership, просто меняется
 * какое-то свойство, например, deleted, на true. А возможно есть спец таблица, где хранятся id групп, приглашения в которые пользователь не хочет получать.
 * Пока что я опущу это, а потом, может быть, вернусь
 */

class Membership {
    use \App\Domain\Model\EntityTrait;
    
    private Group $_group;
    
    private string $memberId;
    private string $initiatorId;

    private bool $accepted = false;
    
    function __construct(Group $group, User $initiator, User $user) {
        $this->id = (string)\Ulid\Ulid::generate(true);

        $this->memberId = $user->id();
        $this->initiatorId = $initiator->id();
        $this->_group = $group;
        
//        if($group->isManager($user)) { /* Этот код будет нужен, если пользователь будет оставать менеджером уйдя из группы. Менеджер группы сможет вернуться в группу без подтверждения */
//            $this->accepted = true;
//        }
        if($initiator->equals($user) && $group->autoApprovalsAreEnabled()) { /* Автоподтверждение работате только, если пользователь сам хочет вступить */
            $this->accepted = true;
        }
        $this->createdAt = new \DateTime("now");
    }
    
    function memberId(): string {
        return $this->memberId;
    }
    
    function accept(User $user): void {
        // если кто-то пригласил
        if($this->initiatorId !== $this->memberId && $user->id() !== $this->memberId) {
            throw new \App\Domain\Model\DomainException("Only user who was offered can accept membership request");
        }
        // если пользователь сам подал заявку на вступление, то только менеджер может принять заявку
        elseif($this->initiatorId === $this->memberId && !$this->_group->isManager($user)) {
            throw new \App\Domain\Model\DomainException("No rights to accept membership request");
        }
        $this->accepted = true;
    }
    
    /* Этот метод понадобится, если авторизаци будет вне этого класса */
//    function accept(): void {
//        $this->accepted = true;
//    }
    
    function isAccepted(): bool {
        return $this->accepted;
    }
}
