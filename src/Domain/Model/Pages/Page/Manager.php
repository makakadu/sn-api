<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Page;

use App\Domain\Model\Users\User\User;

/*
 * Если пользователь является менеджером группы, то он имеет все полномочия обычного участника
 */
class Manager {
    use \App\Domain\Model\EntityTrait;

    private Page $page;
    private User $manager;
    private string $position;
    private bool $showInContacts;
    private bool $externalActivityIsAllowed;

    public function __construct(Page $page, User $manager, string $position, bool $showInContacts, bool $allowExternalActivity) {
        if($position === "admin" && !$allowExternalActivity) {
            throw new DomainException("If manager's position is 'admin' external ectivity should be allowed by default");
        }
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->page = $page;
        $this->manager = $manager;
        $this->position = $position;
        $this->showInContacts = $showInContacts;
        $this->externalActivityIsAllowed = $allowExternalActivity;
        $this->createdAt = new \DateTime('now');
    }
    
    public function position(): string {
        return $this->position;
    }

    function changePosition(User $initiator, string $newPosition): void {
//        if($this->manager->equals($initiator)) { // В вк админ может изменить свою позицию на более низкую
//            throw new \App\Domain\Model\DomainException("Manager cannot change his position");
//        }

        if(!$this->page->isAdmin($initiator)) {
            throw new \App\Domain\Model\DomainException("No rights to change position of manager");
        }
        // Если админ редактирует себя, то он может передать любую позицию, то есть он может оставить себя админом, сделать себя редактором и сделать себя модератором. Если он
        // станет редактором или модератором, то не сможет редактировать ни себя ни других менеджеров.

        \Assert\Assertion::inArray($newPosition, ['moder', 'editor', 'admin']);
        $this->position = $newPosition;
    }

    public function changeShowInContacts(): bool {
        return $this->showInContacts;
    }

    function showInContacts(): void {
        $this->showInContacts = true;
    }

    function hideFromContacts(): void {
        $this->showInContacts = false;
    }
}