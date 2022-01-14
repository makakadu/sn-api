<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection;

use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\AlbumPhoto\AlbumPhoto;
use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ulid\Ulid;
use Assert\Assertion;
use App\Domain\Model\DomainException;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;
use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\AccessLevels as AL;


/* Не получится сделать массив, где элемент будет состоять из типа сущности и ID сущности, потому что элементов может быть очень много. Это значит, что нужна коллекция.
 * Поскольку все сущности не унаследованы от одного родителя, то это не может быть коллекция с этими сущностиями. Поэтому нужно придумать что-то другое. Вот несколько вариантов:
 * 1. Сделать несколько коллекций. Проблемы такого подхода:
 *  - Не знаю как соблюсти порядок сущностей
 *  - При добавлении новой сущности нужно добавлять новую коллекцию, то есть изменить класс, который содержит все эти коллекции
 * 2. Сделать связующую сущность для каждой сущности, у всех связующих сущностей будет общий родитель. Минусы:
 *  - Много классов, таблиц
 */

class SavesCollection {
    use EntityTrait;
    
    const NAME_MAX_LENGTH = 50;
    const MAX_ITEMS_NUMBER = 1000;
    
    protected User $creator;
    protected string $name;
    protected string $description;
    protected PrivacySettings $whoCanSee;
    /** @var Collection<int, SavedItem> $items */
    private Collection $items;
    private ?\DateTime $deletedAt = null;
            
    /** @param array<mixed> $privacy */
    function __construct(User $creator, string $name, string $description, array $privacy) {
        $this->id = (string)Ulid::generate(true);
        $this->creator = $creator;
        
        $this->changeName($name);
        $this->description = $description;
        
        $this->items = new ArrayCollection();
        
        $this->whoCanSee = new PrivacySettings(
            $this,
            isset($privacy['access_level']) ? $privacy['access_level'] : AL::NOBODY,
            isset($privacy['lists']) ? $privacy['lists'] : []
        );
        $this->createdAt = new \DateTime('now');
    }
    
//    function addItem(\App\Domain\Model\Saveable $saveable): void {
//        $visitor = new \App\Domain\Model\CreateSavedCollectionItem();
//        $item = $saveable->acceptSaveableVisitor($visitor);
//        $this->items->add($item);
//    }
    
    function addItem(SavedItem $item): void {
        $maxItemsNumber = self::MAX_ITEMS_NUMBER;
        if($this->items->count() >= $maxItemsNumber) {
            throw new \App\Domain\Model\DomainException("Max amount ($maxItemsNumber) of items in collection reached");
        }
        $this->items->add($item);
    }

    function name(): string {
        return $this->name;
    }

    function itemsCount(): int {
        return $this->items->count();
    }
    
    function description(): string {
        return $this->description;
    }
            
    function isDeleted(): bool {
        return false;
    }
    
    function creator(): User {
        return $this->creator;
    }
    
    public function whoCanSee(): PrivacySettings {
        return $this->whoCanSee;
    }
    
    function changeName(string $name): void {
        Assertion::maxLength(
            $name, self::NAME_MAX_LENGTH,
            sprintf("Max length of description %s", self::NAME_MAX_LENGTH)
        );
        $this->name = $name;
    }

    
}