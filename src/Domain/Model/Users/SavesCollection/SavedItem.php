<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection;

use App\Domain\Model\EntityTrait;
use App\Domain\Model\Saveable;

/* Не получится сделать массив, где элемент будет состоять из типа сущности и ID сущности, потому что элементов может быть очень много. Это значит, что нужна коллекция.
 * Поскольку все сущности не унаследованы от одного родителя, то это не может быть коллекция с этими сущностиями. Поэтому нужно придумать что-то другое. Вот несколько вариантов:
 * 1. Сделать несколько коллекций. Проблемы такого подхода:
 *  - Не знаю как соблюсти порядок сущностей
 *  - При добавлении новой сущности нужно добавлять новую коллекцию, то есть изменить класс, который содержит все эти коллекции
 * 2. Сделать связующую сущность для каждой сущности, у всех связующих сущностей будет общий родитель. Минусы:
 *  - Много классов, таблиц
 * 
 * В любом случае есть минусы, если будет использоваться второй способ, то проблема будет в авторизации. Для каждой сущности нужен класс авторизации, а это значит, что 
 * в случае добавления новой сущности нужно изменить класс, который содержит классы авторизации, чтобы добавив новый класс. Эту проблему можно решить очень бАнально, нужно
 * создать фабрику, которая создаёт нужный класс авторизации на основе имени класса сущности, но не всё так просто, дело в том, что сейчас есть 3 класса с именем AlbumPhoto,
 * различия только в полном имени: App\Domain\Model\Users\AlbumPhoto\AlbumPhoto, App\Domain\Model\Group\AlbumPhoto\AlbumPhoto, App\Domain\Model\Pages\AlbumPhoto\AlbumPhoto,
 * чтобы создать класс авторизации нужно, чтобы код, который анализирует имена классов, знал о словах Users, Groups и Pages в полном имени и отталкивался от них. И это ещё не
 * всё, для авторизации могут понадобиться и другие объекты, например, репозитории, PrivacyService, которые нужно внедрить в сервисы авторизации, а конструкторы сервисов
 * авторизации будут требовать разные наборы зависимостей. Получается, что проще всего смириться с тем, что при добавлении новой сущности нужно будет добавить новый класс
 * авторизации. Это, наверное, можно решить только, если сущность, доступ к которой проверяется, будет содержать логику авторизации или если при авторизации не будут
 * использоваться классы авторизации
 */

abstract class SavedItem {
    
    protected SavesCollection $collection;
    protected int $id;
    protected string $originalId;
    protected string $type;
    protected \DateTime $createdAt;
    protected \DateTime $originalCreatedAt;
    
    public function __construct(SavesCollection $collection, \DateTime $originalCreatedAt, string $type) {
        $this->collection = $collection;
        $this->createdAt = new \DateTime('now');
        $this->originalCreatedAt = $originalCreatedAt;
        $this->type = $type;
    }
    
    /**
     * @template T
     * @param SavedItemVisitor <T> $visitor
     * @return T
     */
    abstract function acceptItemVisitor(SavedItemVisitor $visitor);
    
    abstract function saved(): ?Saveable;
    
}