<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\User;

use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\AccessLevels as AL;
use App\Domain\Model\Users\ComplexPrivacySettingTrait;
use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;
use LogicException;
use OutOfRangeException;
use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;

class ProfileComplexPrivacySetting implements \App\Domain\Model\Users\ComplexPrivacySetting {
    use EntityTrait;
    use ComplexPrivacySettingTrait;
    
    private ProfilePrivacySettings $container;
    private string $name;
    /** @var array<int> $supportedLevels */
    private array $supportedLevels;

    /**
     * @param array<int> $supportedLevels
     */
    function __construct(
        ProfilePrivacySettings $container, string $name,
        int $accessLevel,
        array $supportedLevels = [AL::NOBODY, AL::SOME_CONNECTIONS_AND_LISTS, AL::CONNECTIONS, AL::CONNECTIONS_OF_CONNECTIONS, AL::EVERYONE] // по умолчанию доступны все
    ) {
        $this->container = $container;
        // $this->failIfOutOfRange($accessLevel); // Возможно эта проверка и не нужна, потому что есть $supportedLevels
        $this->checkSupportedLevels($supportedLevels); // на данный момент есть 5 уровней доступа, поэтому в массиве $supportedLevels должны быть значения в диапазоне 1-5
        $this->supportedLevels = $supportedLevels;
        $this->failIfUnsupportedLevel($accessLevel); // Здесь проверяется находится ли $accessLevel в диапазоне цифр, которые указаны в $supportedLevels
        
        $this->name = $name;
        $this->accessLevel = $accessLevel;
        
        $this->setLists(new ArrayCollection(), new ArrayCollection(), new ArrayCollection(), new ArrayCollection());
    }
    
    /**
     * @param ArrayCollection<int, Connection> $allowedConnections
     * @param ArrayCollection<int, Connection> $unallowedConnections
     * @param ArrayCollection<int, ConnectionsList> $allowedLists
     * @param ArrayCollection<int, ConnectionsList> $unallowedLists
     */
    function edit(
        int $accessLevel,
        $allowedConnections, $unallowedConnections,
        $allowedLists, $unallowedLists
    ): void {
        //$this->failIfOutOfRange($accessLevel);
        $this->failIfUnsupportedLevel($accessLevel);
        $this->accessLevel = $accessLevel;
        $this->verifyComplianceToAccessLevel($accessLevel, $allowedConnections, $unallowedConnections, $allowedLists, $unallowedLists);
        $this->setLists($allowedConnections, $unallowedConnections, $allowedLists, $unallowedLists);
    }
    
    private function failIfUnsupportedLevel(int $accessLevel): void {
        if(!in_array($accessLevel, $this->supportedLevels)) {
            $name = $this->name;
            throw new LogicException("Access level '$accessLevel' is unsupported for '$name' privacy setting");
        }        
    }
    
    /**
     * @param array<int> $supportedLevels
     * @throws OutOfRangeException
     */
    private function checkSupportedLevels(array $supportedLevels): void {
        foreach ($supportedLevels as $level) {
            if($level < AL::NOBODY || $level > AL::EVERYONE) {
                $min = AL::NOBODY;
                $max = AL::EVERYONE;
                throw new OutOfRangeException("'$level' is incorrect value for 'supported access levels', minimal supported access level is '$min' and maximal is '$max'");
            }
        }
    }
    
    function ownerId(): string {
        return $this->container->user()->id();
    }
}
