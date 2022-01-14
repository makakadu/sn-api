<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Group;

use App\Domain\Model\Users\User\User;

/*
 * Если пользователь является менеджером группы, то он имеет все полномочия обычного участника
 */
class Manager {
    use \App\Domain\Model\EntityTrait;

    private Group $_group;
    private User $manager;
    private string $position;
    private bool $showInContacts;
    private bool $accepted = false;

    public function __construct(Group $group, User $manager, string $position, bool $showInContacts) {
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->_group = $group;
        $this->manager = $manager;
        $this->changePosition($position);
        $this->showInContacts = $showInContacts;
        $this->createdAt = new \DateTime('now');
    }
    
    function accept(User $initiator): void {
        if(!$this->manager->equals($initiator)) {
            throw new \App\Domain\Model\DomainException("No rights to accept offer to be a manager. Only user who was offered can accept");
        }
        // Возможно, если пользователь уже не является участником, нужно будет удалить предложение
        $this->accepted = true;
    }

    function changePosition(string $level): void {
        \Assert\Assertion::inArray($level, ['moder', 'editor', 'admin']);
        $this->position = $level;
    }

    public function position(): string {
        return $this->position;
    }

    public function showInContacts(): bool {
        return $this->showInContacts;
    }


}