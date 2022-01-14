<?php
declare(strict_types=1);
namespace App\Domain\Model\Users;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Users\AccessLevels as AL;
use Assert\Assertion;
use App\Domain\Model\DomainException;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;

trait ComplexPrivacySettingTrait {
    
    private string $ownerId;
    
    private int $accessLevel;

    /** @var Collection<int, Connection> $allowedConnections */
    private Collection $allowedConnections;
    /** @var Collection<int, Connection> $unallowedConnections */
    private Collection $unallowedConnections;
    /** @var Collection<int, ConnectionsList> $allowedLists */
    private Collection $allowedLists;
    /** @var Collection<int, ConnectionsList> $unallowedLists */
    private Collection $unallowedLists;
    
    public function ownerId(): string {
        return $this->ownerId;
    }
    
    function accessLevel(): int {
        return $this->accessLevel;
    }

    /** @return Collection<int, Connection> */
    public function allowedConnections(): Collection {
        /** @var Collection<mixed, Connection> $allowedConns */
        $allowedConns = $this->allowedConnections;
        return $allowedConns;
    }
    
    /** @return Collection<int, Connection> */
    public function unallowedConnections(): Collection {
        /** @var Collection<mixed, Connection> $unallowedConns */
        $unallowedConns = $this->unallowedConnections;
        return $unallowedConns;
    }
    
    /** @return Collection<int, ConnectionsList> */
    public function allowedLists(): Collection {
        /** @var Collection<mixed, ConnectionsList> $allowedLists */
        $allowedLists = $this->allowedLists;
        return $allowedLists;
    }

    /** @return Collection<int, ConnectionsList> */
    public function unallowedLists(): Collection {
        /** @var Collection<mixed, ConnectionsList> $unallowedLists */
        $unallowedLists = $this->unallowedLists;
        return $unallowedLists;
    }
    
    // Методы failIfSameConnectionInBothLists() и failIfSameConnectionsListInBothLists()
    // не должны выбрасывать исключения, если код правильный. Это защитная проверка
    
    /**
     * @param array<Connection> $allowedConnections
     * @param array<Connection> $unallowedConnections
     * @throws \LogicException
     */
    function failIfSameConnectionInBothLists(array $allowedConnections, array $unallowedConnections): void {
        $connectionsIntersections = array_intersect($allowedConnections, $unallowedConnections);
        if(count($connectionsIntersections)) {
            $id = $connectionsIntersections[array_key_first($connectionsIntersections)]->id(); // первый элемент в массиве не обязательно с ключом 0, поэтому нужно узнать ключ первого элемента с помощью array_key_first(), чтобы получить первый элемент
            throw new \LogicException(
                "Incorrect privacy data, connection ($id) cannot be at same time in list with allowed and in list with unallowed connections"
            );
        }
    }
    
    /**
     * @param array<ConnectionsList> $allowedLists
     * @param array<ConnectionsList> $unallowedLists
     * @throws \LogicException
     */
    function failIfSameConnectionsListInBothLists($allowedLists, $unallowedLists): void {
        $connectionsListsIntersections = array_intersect($allowedLists, $unallowedLists);
        if(count($connectionsListsIntersections)) {
            $id = $connectionsListsIntersections[array_key_first($connectionsListsIntersections)]->id();
            throw new \LogicException(
                "Incorrect privacy data, connections list ($id) cannot be at same time in list with allowed and in list with unallowed connections lists"
            );
        }
    }
    
//    private function setLists($allowedConnections, $unallowedConnections, $allowedLists, $unallowedLists): void {
//        $this->setAllowedConnections($allowedConnections);
//        $this->setUnallowedConnections($unallowedConnections);
//        $this->setAllowedLists($allowedLists);
//        $this->setUnallowedLists($unallowedLists);
//    }
    
    private function failIfOutOfRange(int $accessLevel): void {
        if($accessLevel < AL::NOBODY || $accessLevel > AL::EVERYONE) {
            throw new \OutOfRangeException("$accessLevel acces level does not exist");
        }
    }

    /**
     * @param array<int, Connection> $allowedConnections
     * @param array<int, Connection> $unallowedConnections
     * @param array<int, ConnectionsList> $allowedLists
     * @param array<int, ConnectionsList> $unallowedLists
     */
    function verifyComplianceToAccessLevel(int $accessLevel, $allowedConnections, $unallowedConnections, $allowedLists, $unallowedLists): void {
        if($accessLevel === AL::NOBODY) {
            Assertion::allTrue([
                !count($allowedConnections), !count($unallowedConnections),
                !count($allowedLists), !count($unallowedLists)
            ], "If access level is ".AL::NOBODY." then all lists should be empty");
        }
        elseif($accessLevel >= AL::CONNECTIONS && $accessLevel <= AL::EVERYONE) {
            $errorMessage = "If access level is ".AL::CONNECTIONS.", ".AL::CONNECTIONS_OF_CONNECTIONS." or ".AL::EVERYONE." then 'allowedFriendships' and 'allowedLists' should be empty";
            Assertion::allTrue([!count($allowedConnections), !count($allowedLists)], $errorMessage);
        }
        elseif($accessLevel === AL::SOME_CONNECTIONS_AND_LISTS) {
            $errorMessage = "If access level is ".AL::SOME_CONNECTIONS_AND_LISTS." at least one allowed friend or 'list of friends' should be specified";
            Assertion::true(((count($allowedConnections) + !count($allowedLists)) > 0), $errorMessage);
        }
    }
    
    /**
     * @param array<Connection> $allowedConnections
     * @throws \InvalidArgumentException
     */
    private function setAllowedConnections(array $allowedConnections): void {
        if(count($allowedConnections) > 50) {
            throw new \InvalidArgumentException('Number of allowed friends exceeded');
        }
        $this->allowedConnections = new ArrayCollection();
        foreach($allowedConnections as $connection) {
            $this->allowedConnections->add($connection);
        }
    }

    /**
     * @param array<Connection> $unallowed
     * @throws \InvalidArgumentException
     */
    private function setUnallowedConnections(array $unallowed): void {
        if(count($unallowed) > 50) {
            throw new \InvalidArgumentException('Number of unallowed friends exceeded');
        }
        $this->unallowedConnections = new ArrayCollection();
        foreach($unallowed as $connection) {
            $this->unallowedConnections->add($connection);
        }
    }
    
    /**
     * @param array<ConnectionsList> $allowedLists
     */
    private function setAllowedLists(array $allowedLists): void {
        $this->allowedLists = new ArrayCollection();
        foreach($allowedLists as $list) {
            $this->allowedLists->add($list);
        }
    }
    
    /**
     * @param array<ConnectionsList> $unallowedLists
     */
    private function setUnallowedLists(array $unallowedLists): void {
        $this->unallowedLists = new ArrayCollection();
        foreach($unallowedLists as $list) {
            $this->unallowedLists->add($list);
        }
    }

    /**
     * @param ArrayCollection<int, Connection> $allowedConnections
     * @param ArrayCollection<int, Connection> $unallowedConnections
     * @throws DomainException
     */
    function checkConnectionsDuplicates(
        ArrayCollection $allowedConnections,
        ArrayCollection $unallowedConnections,
        string $message
    ): void {
        if(array_intersect($allowedConnections->toArray(), $unallowedConnections->toArray())) {
            throw new DomainException($message);
        }
    }
    
    /**
     * @param array<int, Connection> $allowedConnections
     * @param array<int, Connection> $unallowedConnections
     * @param array<int, ConnectionsList> $allowedLists
     * @param array<int, ConnectionsList> $unallowedLists
     */
    function setLists($allowedConnections, $unallowedConnections, $allowedLists, $unallowedLists): void {
        foreach($allowedConnections as $allowedConn) {
            $this->allowedConnections->add($allowedConn);
        }
        foreach($unallowedConnections as $unallowedConn) {
            $this->allowedConnections->add($unallowedConn);
        }
        foreach($allowedLists as $allowedList) {
            $this->allowedLists->add($allowedList);
        }
        foreach($unallowedLists as $unallowedList) {
            $this->unallowedLists->add($unallowedList);
        }
    }
//    
//    /**
//     * @param ArrayCollection<int, Connection> $allowedConnections
//     * @param ArrayCollection<int, Connection> $unallowedConnections
//     * @param ArrayCollection<int, ConnectionsList> $allowedLists
//     * @param ArrayCollection<int, ConnectionsList> $unallowedLists
//     */
//    function setLists($allowedConnections, $unallowedConnections, $allowedLists, $unallowedLists): void {
//        $this->allowedConnections = $allowedConnections;
//        $this->unallowedConnections = $unallowedConnections;
//        $this->allowedLists = $allowedLists;
//        $this->unallowedLists = $unallowedLists;
//    }
    
    function toCollection() {
        
    }
}
