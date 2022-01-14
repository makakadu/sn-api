<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Subscription;

use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\DomainException;

class Subscription {
    use \App\Domain\Model\EntityTrait;

    const HOUR = "hour";
    const DAY = "day";
    const WEEK = "week";
    const MONTH = "month";
    const FOREVER = "forever";
    
    protected string $pageId;
    protected string $userId;
    //private bool $isDeleted = false;
    //private ?\DateTime $deletedAt;
    private bool $isDisabled; /*
     * Если это свойство true, то пользователь подписан, если false, то отписан. Сейчас мне кажется, что удалять Subscription не стоит, как минимум есть одна причина - можно
     * показывать список бывших подписок. Также это может понадобится для анализа, статистики, истории. Конечно можно "архивировать" подписки, их нельзя будет восстановить,
     * но это не самое лучшее решение, потому что для возобновления подписки нужно будет создавать новую, а это не очень хорошо, потому что подписок в теории будет очень много
     */
    
    /*
     * При создании Subscription эти 2 значения равны null. Это значит, что никакой паузы нет.
     * Если нужно сделать паузу, то в $pauseStart будет засетано new \DateTime('now'), а в $period 1 из 5 периодов.
     * Подписка будет на паузе только в случае, если new \DateTime('now') будет меньше, чем $pauseStart + период. Если период === "forever", то к $pauseStart
     * добавляем очень большое число и получается, что оно всегда будет больше, то есть \DateTime('now') < ($pauseStart + "forever") всегда true.
     */
    private ?\DateTime $pauseStart = null;
    private ?string $pausePeriod = null;
    
    function __construct(Page $page, User $user) {
        $this->id = (string)\Ulid\Ulid::generate(true);
        
        $this->pageId = $page->id();
        $this->userId = $user->id();

        $this->createdAt = new \DateTime("now");
    }
    
    /* Если пользователь хочет отписаться, то нет смысла удалять подписку, лучше сохранить эту, потому что она может пригодиться в будущем, наверное */
    function disable(User $initiator): void {
        if($this->userId !== $initiator->id()) {
            throw new DomainException("No rights to disable subscription");
        }
        if($this->isDisabled) {
            throw new DomainException("Already disabled");
        }
        $this->isDisabled = true;
        $this->pauseStart = null; // Если пользователь отписывается, то пауза обнуляется
        $this->pausePeriod = null;
    }
    
    function enable(User $initiator): void {
        if($this->userId !== $initiator->id()) {
            throw new DomainException("No rights to enable subscription");
        }
        if(!$this->isDisabled) {
            throw new DomainException("Already enabled");
        }
        $this->isDisabled = false;
    }
    
    function pause(User $initiator, string $period): void {
        if($this->userId !== $initiator->id()) {
            throw new DomainException("No rights to pause subscription");
        }
        if($this->isDisabled) {
            throw new DomainException("Cannot pause subscription if it is disabled");
        }
        if(!\in_array($period, ['hour', 'day', 'week', 'month', 'forever'])) {
            throw new \InvalidArgumentException("Incorrect period");
        }
        $this->pauseStart = new \DateTime("now");
        $this->pausePeriod = $period;
    }

    function unpause(User $initiator): void {
        if($this->userId !== $initiator->id()) {
            throw new DomainException("No rights to unpause subscription");
        }
        if($this->isDisabled) {
            throw new DomainException("Cannot unpause subscription if it is disabled");
        }
        $this->pause = new \DateTime("now");
    }
}
